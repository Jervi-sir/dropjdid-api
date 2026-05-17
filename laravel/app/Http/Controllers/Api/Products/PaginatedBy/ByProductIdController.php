<?php

namespace App\Http\Controllers\Api\Products\PaginatedBy;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ByProductIdController extends Controller
{
    public function __invoke(Request $request, int $product_id): JsonResponse
    {
        $userId = $request->user()?->id;

        $products = Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            ->where('id', '!=', $product_id)
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->inRandomOrder()
            ->simplePaginate(10);

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => [
                'id' => $product->id,
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'images' => $product->images->sortBy('sort_order')->pluck('image')->values()->all(),
                'price' => (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'user' => [
                    'id' => $product->store?->id,
                    'username' => $product->store?->store_name ?? $product->store?->user?->username,
                ],
                'is_saved' => $userId !== null && $product->savedProducts->isNotEmpty(),
            ]),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
