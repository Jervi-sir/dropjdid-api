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
use Illuminate\Support\Collection;

class ProductsFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'label_id' => ['nullable', 'integer', 'exists:labels,id'],
            'section' => ['nullable', 'string', 'in:random'],
            'products_page' => ['nullable', 'integer', 'min:1'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        if (isset($validated['label_id'])) {
            return $this->productsForLabel(
                $validated['label_id'],
                $validated['products_page'] ?? $validated['page'] ?? 1,
                $validated['products_per_page'] ?? $validated['per_page'] ?? 10,
                $userId,
                $user,
                $validated['ads_count'] ?? 4,
            );
        }

        if (($validated['section'] ?? null) === 'random') {
            return $this->randomProductsPayload(
                $validated['products_page'] ?? $validated['page'] ?? 1,
                $validated['products_per_page'] ?? $validated['per_page'] ?? 10,
                $userId,
                $user,
            );
        }

        $labelPage = $validated['page'] ?? 1;
        $adsCount = $validated['ads_count'] ?? 4;
        $labelsPerPage = min($validated['per_page'] ?? 4, 4);
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

        foreach ($labelPaginator->getCollection() as $label) {
            $payload = $this->productsPayloadForLabel($label->id, 1, $productsPerPage, $userId, $user);
            $payloads[$label->id] = $payload;
            foreach ($payload['paginator']->items() as $product) {
                $allProducts->push($product);
            }
        }

        Product::loadFeedRelations($allProducts, $userId);

        $labelSections = $labelPaginator->getCollection()
            ->map(function (Label $label) use ($payloads, $user): array {
                $payload = $payloads[$label->id];
                $formattedProducts = collect($payload['paginator']->items())
                    ->map(fn (Product $product): array => $product->formatProduct($product, $user));

                return $label->formatFeedSection(
                    [
                        'data' => $formattedProducts,
                        'next_page' => $payload['next_page'],
                    ],
                    $label->saved_labels_count,
                    (bool) $label->is_liked,
                );
            })
            ->values();

        return response()->json([
            'data' => $this->buildMixedFeedItems(
                $labelSections,
                $adsCount,
            )->values()->all(),
            'next_page' => $labelPaginator->hasMorePages() ? $labelPaginator->currentPage() + 1 : null,
        ]);
    }

    public function labelProducts(Request $request, Label $label): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        return $this->productsForLabel(
            $label->id,
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 10,
            $userId,
            $user,
            $validated['ads_count'] ?? 4
        );
    }

    /**
     * @param  Collection<int, array>  $labelSections
     * @return Collection<int, array>
     */
    private function buildMixedFeedItems(Collection $labelSections, int $adsCount): Collection
    {
        return Advertisement::injectIntoFeed($labelSections->values(), interval: 2, adsCount: $adsCount)->values();
    }

    private function productsForLabel(int $labelId, int $page, int $perPage, ?int $userId, $user, int $adsCount = 4): JsonResponse
    {
        $payload = $this->productsPayloadForLabel($labelId, $page, $perPage, $userId, $user);

        Product::loadFeedRelations($payload['paginator'], $userId);

        $formattedData = collect($payload['paginator']->items())
            ->map(fn (Product $product): array => $product->formatProduct($product, $user));

        $data = Advertisement::injectIntoFeed($formattedData, adsCount: $adsCount)->values();

        return response()->json([
            'data' => $data,
            'liked_products_count' => $this->labelLikedProductsCount($labelId, $userId),
            'next_page' => $payload['next_page'],
        ]);
    }

    private function randomProductsPayload(int $page, int $perPage, ?int $userId, $user): JsonResponse
    {
        $payload = $this->formattedRandomProductsPayload($page, $perPage, $userId, $user);

        return response()->json([
            'data' => $payload['data']->values()->all(),
            'next_page' => $payload['next_page'],
        ]);
    }

    /**
     * @return array{data: Collection<int, array>, next_page: ?int}
     */
    private function formattedRandomProductsPayload(int $page, int $perPage, ?int $userId, $user): array
    {
        $paginator = $this->baseProductsQuery()
            ->inRandomOrder()
            ->simplePaginate($perPage, ['*'], 'page', $page);

        Product::loadFeedRelations($paginator, $userId);

        return [
            'data' => collect($paginator->items())
                ->map(fn (Product $product): array => $product->formatProduct($product, $user)),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    /**
     * @return array{paginator: Paginator, next_page: ?int}
     */
    private function productsPayloadForLabel(int $labelId, int $page, int $perPage, ?int $userId, $user): array
    {
        $paginator = $this->baseProductsQuery()
            ->whereHas('productKeywords', fn ($query) => $query->where('label_id', $labelId))
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
            ->where('status', 'published');
    }

    private function labelLikedProductsCount(int $labelId, ?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }

        return LikedProduct::query()
            ->where('user_id', $userId)
            ->whereHas('product.productKeywords', fn ($query) => $query->where('label_id', $labelId))
            ->count();
    }
}
