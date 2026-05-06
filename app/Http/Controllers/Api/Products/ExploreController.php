<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'label_id' => ['nullable', 'integer', 'exists:labels,id'],
            'products_page' => ['nullable', 'integer', 'min:1'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if (isset($validated['label_id'])) {
            return $this->productsForLabel(
                $request,
                $validated['label_id'],
                $validated['products_page'] ?? $validated['page'] ?? 1,
                $validated['products_per_page'] ?? $validated['per_page'] ?? 10,
            );
        }

        $perPage = $validated['per_page'] ?? 10;
        $productsPerPage = $validated['products_per_page'] ?? 10;

        $labels = Label::query()
            ->has('keywords.productKeywords')
            ->orderBy('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $labels->getCollection()->map(fn (Label $label): array => [
                'id' => $label->id,
                'name' => $label->en ?? $label->code,
                'liked_products_count' => $this->labelLikedProductsCount($label),
                'products' => $this->labelProductsPayload($request, $label, 1, $productsPerPage),
            ])->values(),
            'next_page' => $labels->hasMorePages() ? $labels->currentPage() + 1 : null,
        ]);
    }

    private function productsForLabel(Request $request, int $labelId, int $page, int $perPage): JsonResponse
    {
        $label = Label::query()->findOrFail($labelId);

        return response()->json([
            'data' => $this->labelProductsPayload($request, $label, $page, $perPage),
        ]);
    }

    private function labelProductsPayload(Request $request, Label $label, int $page, int $perPage): array
    {
        $userId = $request->user()?->getAuthIdentifier();

        $products = Product::query()
            ->where('status', 'published')
            ->whereHas('productKeywords', fn ($query) => $query->where('label_id', $label->id))
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->latest()
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $products->getCollection()->map(fn (Product $item): array => [
                'id' => $item->id,
                'price' => (float) ($item->show_price ?? $item->store_price ?? $item->original_price ?? 0),
                'image' => $item->images->sortBy('sort_order')->first()?->image,
                'user' => [
                    'id' => $item->store?->user?->id,
                    'name' => $item->store?->user?->username,
                ],
                'is_saved' => $request->user() !== null && $item->savedProducts->isNotEmpty(),
            ])->values(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ];
    }

    private function labelLikedProductsCount(Label $label): int
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('productKeywords', fn ($query) => $query->where('label_id', $label->id))
            ->withCount('likedProducts')
            ->get()
            ->sum('liked_products_count');
    }
}
