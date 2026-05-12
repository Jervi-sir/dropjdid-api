<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class GetOrderStatusController extends Controller
{
    public function __invoke(Request $request, $orderId)
    {
        $order = Order::with(['items.product.images', 'paymentMethod'])
            ->where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'order' => $order->formatForDetail(),
            'permissions' => [
                'can_see_order_info' => true,
                'can_cancel' => in_array($order->status, ['pending', 'confirmed']),
                'can_claim_issue' => $order->status === 'delivered',
                'can_delete_from_history' => in_array($order->status, ['delivered', 'cancelled', 'returned']),
            ],
        ]);
    }
}
