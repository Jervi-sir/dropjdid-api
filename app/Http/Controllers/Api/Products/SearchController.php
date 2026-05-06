<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $userId = $request->user()?->getAuthIdentifier();
        $query = trim($validated['query']);

        $products = Product::query()
            ->where('status', 'published')
            ->where('name', 'like', '%'.$query.'%')
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($builder) use ($userId) {
                    return $userId === null
                        ? $builder->whereRaw('1 = 0')
                        : $builder->where('user_id', $userId);
                },
            ])
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
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
        ]);
    }
}
