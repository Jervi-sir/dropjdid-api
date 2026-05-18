<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\LikedProduct;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get performance stats and paginated lists of who liked/saved/dropped a product.
     */
    public function __invoke(Request $request, Product $product): JsonResponse
    {
        // 1. KPI Counts
        $likedCount = LikedProduct::where('product_id', $product->id)->count();
        $savedCount = SavedProduct::where('product_id', $product->id)->count();
        $ordersCount = OrderItem::where('product_id', $product->id)->count();
        $dropsCount = $product->drops()->count();

        // 2. Who Liked this Product (Paginated Users)
        $likedPerPage = $request->input('liked_per_page', 10);
        $likedPage = LikedProduct::where('product_id', $product->id)
            ->with(['user'])
            ->latest()
            ->paginate($likedPerPage, ['*'], 'liked_page')
            ->withQueryString();

        $formattedLiked = $likedPage->through(fn ($like) => [
            'id' => $like->id,
            'user' => $like->user ? [
                'id' => $like->user->id,
                'full_name' => $like->user->full_name,
                'username' => $like->user->username,
                'email' => $like->user->email,
                'image' => $like->user->image,
            ] : null,
            'created_at' => $like->created_at?->toIso8601String(),
        ]);

        // 3. Who Saved this Product (Paginated Users)
        $savedPerPage = $request->input('saved_per_page', 10);
        $savedPage = SavedProduct::where('product_id', $product->id)
            ->with(['user'])
            ->latest()
            ->paginate($savedPerPage, ['*'], 'saved_page')
            ->withQueryString();

        $formattedSaved = $savedPage->through(fn ($save) => [
            'id' => $save->id,
            'user' => $save->user ? [
                'id' => $save->user->id,
                'full_name' => $save->user->full_name,
                'username' => $save->user->username,
                'email' => $save->user->email,
                'image' => $save->user->image,
            ] : null,
            'created_at' => $save->created_at?->toIso8601String(),
        ]);

        // 4. Drops carrying this Product (Paginated Drops + Pivot Drop Price + Product Orders Count per Drop)
        $dropsPerPage = $request->input('drops_per_page', 10);
        $dropsPage = $product->drops()
            ->latest()
            ->paginate($dropsPerPage, ['*'], 'drops_page')
            ->withQueryString();

        $formattedDrops = $dropsPage->through(function ($drop) use ($product) {
            // Count total orders/items for this product on this drop
            $ordersOnDropCount = OrderItem::where('product_id', $product->id)
                ->where('drop_id', $drop->id)
                ->count();

            return [
                'id' => $drop->id,
                'title' => $drop->title,
                'starts_at' => $drop->starts_at?->toIso8601String(),
                'ends_at' => $drop->ends_at?->toIso8601String(),
                'status' => $drop->status,
                'drop_price' => $drop->pivot->drop_price ?? null,
                'orders_count' => $ordersOnDropCount,
            ];
        });

        return response()->json([
            'kpis' => [
                'liked_count' => $likedCount,
                'saved_count' => $savedCount,
                'orders_count' => $ordersCount,
                'drops_count' => $dropsCount,
            ],
            'liked_users' => $formattedLiked,
            'saved_users' => $formattedSaved,
            'drops' => $formattedDrops,
        ]);
    }
}
