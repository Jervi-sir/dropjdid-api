<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'products_page' => ['nullable', 'integer', 'min:1'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $labelPage = $validated['page'] ?? 1;
        $labelsPerPage = $validated['per_page'] ?? 10;
        $productsPerPage = $validated['products_per_page'] ?? 10;

        $labelPaginator = Label::query()
            ->has('keywords.productKeywords')
            ->withCount('savedLabels')
            ->withExists(['savedLabels as is_liked' => function ($query) use ($userId) {
                if ($userId === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('user_id', $userId);
                }
            }])
            ->orderBy('id')
            ->simplePaginate($labelsPerPage, ['*'], 'page', $labelPage);

        $payloads = [];
        $allProducts = collect();

        $originalCollection = $labelPaginator->getCollection();
        $repeatedCollection = collect();
        if ($originalCollection->isNotEmpty()) {
            $count = 0;
            while ($repeatedCollection->count() < 10) {
                foreach ($originalCollection as $item) {
                    if ($repeatedCollection->count() >= 10) {
                        break;
                    }
                    $cloned = clone $item;
                    $cloned->original_id = $item->id;
                    $cloned->id = $item->id * 1000 + $count;
                    $repeatedCollection->push($cloned);
                    $count++;
                }
            }
        }

        foreach ($repeatedCollection as $label) {
            $payload = $this->productsPayloadForLabel($label->original_id, $validated['products_page'] ?? 1, $productsPerPage);
            $payloads[$label->id] = $payload;
            foreach ($payload['paginator']->items() as $product) {
                $allProducts->push($product);
            }
        }

        Product::loadFeedRelations($allProducts, $userId);

        $labelSections = $repeatedCollection
            ->filter(function (Label $label) use ($payloads): bool {
                return !$payloads[$label->id]['paginator']->isEmpty();
            })
            ->map(function (Label $label) use ($payloads, $user): array {
                $payload = $payloads[$label->id];
                $formattedProducts = collect($payload['paginator']->items())
                    ->map(fn(Product $product): array => $product->formatProduct($product, $user));

                return $label->formatFeedSection(
                    [
                        'data' => $formattedProducts,
                        'next_page' => $payload['next_page'],
                    ],
                    $label->saved_labels_count ?? 0,
                    (bool) $label->is_liked,
                );
            })
            ->values();

        $adsCount = $validated['ads_count'] ?? 4;
        $data = Advertisement::injectIntoFeed($labelSections, interval: 2, adsCount: $adsCount)->values();

        return response()->json([
            'data' => $data,
            'next_page' => $labelPaginator->hasMorePages() ? $labelPaginator->currentPage() + 1 : null,
        ]);
    }

    public function labelProducts(Request $request, Label $label): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $payload = $this->productsPayloadForLabel(
            $label->id,
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 10
        );

        Product::loadFeedRelations($payload['paginator'], $userId);

        $formattedData = collect($payload['paginator']->items())
            ->map(fn(Product $product): array => $product->formatProduct($product, $user));

        $adsCount = $validated['ads_count'] ?? 4;

        $data = Advertisement::injectIntoFeed($formattedData, interval: 2, adsCount: $adsCount)->values();

        return response()->json([
            'data' => $data,
            'liked_products_count' => $this->labelLikedProductsCount($label->id, $userId),
            'next_page' => $payload['next_page'],
        ]);
    }

    /**
     * @return array{paginator: Paginator, next_page: ?int}
     */
    private function productsPayloadForLabel(int $labelId, int $page, int $perPage): array
    {
        $paginator = $this->baseProductsQuery()
            ->whereHas('productKeywords', fn($query) => $query->where('label_id', $labelId))
            ->latest('id')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'paginator' => $paginator,
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    private function baseProductsQuery()
    {
        return Product::query()
            ->where('status', Product::STATUS_PUBLISHED);
    }

    private function labelLikedProductsCount(int $labelId, ?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }

        return LikedProduct::query()
            ->where('user_id', $userId)
            ->whereHas('product.productKeywords', fn($query) => $query->where('label_id', $labelId))
            ->count();
    }
}
