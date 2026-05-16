<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class ShowProductController extends Controller
{
    public function __invoke(int $store_id, int $product_id): JsonResponse
    {
        $store = Store::find($store_id);
        $product = Product::find($product_id);
        $product->load([
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'variants',
            'productKeywords',
            'keywords',
            'category',
            'quality',
            'paymentMethod',
            'gender',
            'store.user',
        ]);

        $product->loadCount([
            'savedProducts',
            'likedProducts',
            'drops',
        ]);

        $product->loadSum('orderItems', 'quantity');

        return response()->json([
            'status' => 'success',
            'data' => array_merge($product->toArray(), [
                'is_rejected' => $product->status === Product::STATUS_REJECTED,
                'status' => Product::STATUSES[$product->status] ?? 'unknown',
                'status_text' => Product::STATUSES[$product->status] ?? 'unknown',
                'rejection_reason' => collect($product->rejection_reason)->first(),
            ]),
        ]);
    }
}
