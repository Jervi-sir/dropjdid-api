<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionOrderController extends Controller
{
    public function accept(Request $request, int $storeId, int $orderId): JsonResponse
    {
        $order = $this->getStoreOrder($storeId, $orderId);

        $order->update(['status' => Order::STATUS_CONFIRMED]);

        return response()->json([
            'message' => 'Order accepted successfully.',
            'data' => $order->formatForDetail(),
        ]);
    }

    public function decline(Request $request, int $storeId, int $orderId): JsonResponse
    {
        $order = $this->getStoreOrder($storeId, $orderId);

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Order declined successfully.',
            'data' => $order->formatForDetail(),
        ]);
    }

    public function claim(Request $request, int $storeId, int $orderId): JsonResponse
    {
        $order = $this->getStoreOrder($storeId, $orderId);

        $order->update([
            'has_claim_issue' => true,
            'claim_issue' => $request->input('issue', 'didn_not_receive'),
        ]);

        return response()->json([
            'message' => 'Order claimed successfully.',
            'data' => $order->formatForDetail(),
        ]);
    }

    private function getStoreOrder(int $storeId, int $orderId): Order
    {
        return Order::where('id', $orderId)
            ->whereHas('items.product', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->firstOrFail();
    }
}
