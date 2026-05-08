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

class ProductsController extends Controller
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
            ->orderBy('id')
            ->simplePaginate($labelsPerPage, ['*'], 'page', $labelPage);

        $labelSections = $labelPaginator->getCollection()
            ->map(fn (Label $label): array => $label->formatFeedSection(
                $this->productsPayloadForLabel($label->id, 1, $productsPerPage, $userId, $user),
                $this->labelLikedProductsCount($label->id, $userId),
            ))
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        return $this->productsForLabel(
            $label->id,
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 10,
            $userId,
            $user,
        );
    }

    /**
     * @param  Collection<int, array>  $labelSections
     * @return Collection<int, array>
     */
    private function buildMixedFeedItems(Collection $labelSections, int $adsCount): Collection
    {
        return Advertisement::injectIntoFeed($labelSections->values(), interval: 6, adsCount: $adsCount)->values();
    }

    private function productsForLabel(int $labelId, int $page, int $perPage, ?int $userId, $user): JsonResponse
    {
        $payload = $this->productsPayloadForLabel($labelId, $page, $perPage, $userId, $user);

        return response()->json([
            'data' => $payload['data']->values()->all(),
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
        $paginator = $this->baseProductsQuery($userId)
            ->inRandomOrder()
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (Product $product): array => $product->formatProduct($product, $user)),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
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
            ->where('status', 'published')
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
