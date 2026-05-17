<?php

namespace App\Http\Controllers\Api\Products\PaginatedBy;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ByDropIdController extends Controller
{
    public function __invoke(Request $request, int $drop_id): JsonResponse
    {
        $userId = $request->user()?->id;

        $products = Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            ->whereHas('drops', fn (Builder $query) => $query->where('drops.id', $drop_id))
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
                    'id' => $product->user?->id,
                    'username' => $product->user?->username,
                ],
                'is_saved' => $userId !== null && $product->savedProducts->isNotEmpty(),
            ]),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
