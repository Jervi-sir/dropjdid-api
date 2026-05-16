<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
            ->orderBy('id')
            ->simplePaginate($labelsPerPage, ['*'], 'page', $labelPage);

        $labelSections = $labelPaginator->getCollection()
            ->map(fn (Label $label): array => $label->formatFeedSection(
                $this->productsPayloadForLabel($label->id, 1, $productsPerPage, $userId, $user),
                $this->labelLikedProductsCount($label->id, $userId),
            ))
            ->values();

        $adsCount = $validated['ads_count'] ?? 4;
        $data = Advertisement::injectIntoFeed($labelSections, adsCount: $adsCount)->values();

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
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $payload = $this->productsPayloadForLabel(
            $label->id,
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 10,
            $userId,
            $user
        );

        return response()->json([
            'data' => $payload['data']->values()->all(),
            'liked_products_count' => $this->labelLikedProductsCount($label->id, $userId),
            'next_page' => $payload['next_page'],
        ]);
    }

    /**
     * @return array{data: Collection<int, array>, next_page: ?int}
     */
    private function productsPayloadForLabel(int $labelId, int $page, int $perPage, ?int $userId, $user): array
    {
        $paginator = $this->baseProductsQuery($userId)
            ->whereHas('productKeywords', fn ($query) => $query->where('label_id', $labelId))
            ->latest('id')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (Product $product): array => $product->formatProduct($product, $user)),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    private function baseProductsQuery(?int $userId)
    {
        return Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ]);
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
