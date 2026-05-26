<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListPendingRejectedProductsController extends Controller
{
    public function __invoke(Request $request, int $store_id): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $store = Store::where('id', $store_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $pendingProducts = Product::query()
            ->where('store_id', $store->id)
            ->where('status', Product::STATUS_PENDING)
            ->get(['id', 'name']);

        $rejectedProducts = Product::query()
            ->where('store_id', $store->id)
            ->where('status', Product::STATUS_REJECTED)
            ->get(['id', 'name']);

        $pendingIds = $pendingProducts->pluck('id');
        $rejectedIds = $rejectedProducts->pluck('id');

        $pendingBanner = $pendingProducts->map(fn(Product $product): array => [
            'product_id' => $product->id,
            'title' => $product->name,
        ]);

        $rejectionBanner = $rejectedProducts->map(fn(Product $product): array => [
            'product_id' => $product->id,
            'title' => $product->name,
        ]);

        return response()->json([
            'pending_ids' => $pendingIds,
            'rejected_ids' => $rejectedIds,
            'pending_banner' => $pendingBanner,
            'rejection_banner' => $rejectionBanner,
        ]);
    }
}
