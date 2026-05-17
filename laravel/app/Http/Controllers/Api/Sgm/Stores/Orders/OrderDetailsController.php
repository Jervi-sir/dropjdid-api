<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderDetailsController extends Controller
{
    public function __invoke(Request $request, int $storeId, int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->whereHas('items.product', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->with(['items.size', 'items.product.images', 'paymentMethod'])
            ->firstOrFail();

        return response()->json([
            'data' => $order->formatForDetail(),
        ]);
    }
}