<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ShowProductController extends Controller
{
    public function __invoke(Product $product): JsonResponse
    {
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
                'is_rejected' => $product->status === 'rejected',
            ]),
        ]);
    }
}
