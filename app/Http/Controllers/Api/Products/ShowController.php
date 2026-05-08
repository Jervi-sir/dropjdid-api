<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShowController extends Controller
{
    public function show(Request $request, Product $product): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();

        $product->load([
            'images',
            'drops',
            'variants.size',
            'store.user',
            'likedProducts' => function ($query) use ($userId) {
                return $userId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('user_id', $userId);
            },
            'savedProducts' => function ($query) use ($userId) {
                return $userId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('user_id', $userId);
            },
            'keywords.label',
        ]);

        $labels = $product->keywords
            ->map(fn ($keyword) => $keyword->label)
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($label) => [
                'id' => $label->id,
                'code' => $label->code,
                'en' => $label->en,
                'fr' => $label->fr,
                'ar' => $label->ar,
            ])
            ->all();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'title' => $product->name,
                'images' => $product->images->sortBy('sort_order')->pluck('image')->values()->all(),
                'price' => (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'nb_likes' => $product->likedProducts()->count(),
                'is_liked' => $request->user() !== null && $product->likedProducts->isNotEmpty(),
                'available_sizes' => $product->variants
                    ->map(fn ($variant) => $variant->size?->code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'search_code' => strtolower('sam'.$product->id),
                'description' => $product->description,
                'is_saved' => $request->user() !== null && $product->savedProducts->isNotEmpty(),
                'nb_drops' => $product->drops->count(),
                'labels' => $labels,
                'user' => [
                    'id' => $product->store?->user?->id,
                    'name' => $product->store?->user?->username,
                ],
            ],
        ]);
    }

    public function suggest(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:4'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $userId = $request->user()?->getAuthIdentifier();
        $page = $validated['page'] ?? 1;
        $labelsPerPage = min($validated['per_page'] ?? 4, 4);
        $productsPerPage = $validated['products_per_page'] ?? 10;
        $adsCount = $validated['ads_count'] ?? 3;

        $labelIds = $product->productKeywords()
            ->pluck('label_id')
            ->unique()
            ->values();

        $labelPaginator = Label::query()
            ->whereIn('id', $labelIds)
            ->orderBy('id')
            ->simplePaginate($labelsPerPage, ['*'], 'page', $page);

        $sections = $labelPaginator->getCollection()
            ->map(fn (Label $label): array => $label->formatFeedSection(
                $this->productsPayloadForLabel($product, $label->id, 1, $productsPerPage, $userId, $request->user()),
                $this->labelLikedProductsCount($label->id, $userId),
            ))
            ->values();

        return response()->json([
            'data' => Advertisement::injectIntoFeed($sections->values(), interval: 2, adsCount: $adsCount)->values()->all(),
            'next_page' => $labelPaginator->hasMorePages() ? $labelPaginator->currentPage() + 1 : null,
        ]);
    }

    /**
     * @return array{data: Collection<int, array>, next_page: ?int}
     */
    private function productsPayloadForLabel(Product $product, int $labelId, int $page, int $perPage, ?int $userId, $user): array
    {
        $paginator = Product::query()
            ->where('status', 'published')
            ->whereKeyNot($product->id)
            ->whereHas('productKeywords', fn (Builder $query) => $query->where('label_id', $labelId))
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->latest('id')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (Product $item): array => [
                    'id' => $item->id,
                    'price' => (float) ($item->show_price ?? $item->store_price ?? $item->original_price ?? 0),
                    'image' => $item->images->sortBy('sort_order')->first()?->image,
                    'user' => [
                        'id' => $item->store?->user?->id,
                        'name' => $item->store?->user?->username,
                    ],
                    'is_saved' => $user !== null && $item->savedProducts->isNotEmpty(),
                ]),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    private function labelLikedProductsCount(int $labelId, ?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }

        return LikedProduct::query()
            ->where('user_id', $userId)
            ->whereHas('product.productKeywords', fn (Builder $query) => $query->where('label_id', $labelId))
            ->count();
    }
}
