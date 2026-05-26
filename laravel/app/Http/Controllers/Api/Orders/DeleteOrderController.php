<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DeleteOrderController extends Controller
{
    public function __invoke(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Optional: Check if order can be deleted (e.g. only if cancelled or delivered)
        // But user just said delete order.

        $order->delete();

        return response()->json([
            'message' => 'Order deleted from history successfully.',
        ]);
    }
}
