<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ShowOrderInfoController extends Controller
{
    public function __invoke(Request $request, int $orderId)
    {
        $order = Order::with(['items', 'paymentMethod'])
            ->where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'full_name' => $order->full_name,
                'phone_number' => $order->phone_number,
                'wilaya' => $order->wilaya,
                'baladiya' => $order->baladiya,
                'home_address' => $order->home_address,
                'delivery_method' => $order->delivery_method,
                'delivery_fees' => (float) $order->delivery_fees,
                'subtotal' => (float) $order->subtotal,
                'total' => (float) $order->total,
                'status' => $order->status,
                'type' => $order->paymentMethod?->code === 'online' ? 'online' : 'cod',
                'payment_method' => $order->paymentMethod?->en,
                'is_online' => (bool) $order->paymentMethod?->is_online,
                'items_summary' => $order->items->map(function ($item) {
                    $details = [];
                    if ($item->size) {
                        $details[] = $item->size;
                    }
                    if ($item->color) {
                        $details[] = $item->color;
                    }
                    $detailsStr = ! empty($details) ? ' ('.implode(', ', $details).')' : '';

                    return "{$item->quantity} x {$item->product_name}{$detailsStr}";
                })->implode(', '),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'size' => $item->size,
                        'color' => $item->color,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                    ];
                }),
            ],
        ]);
    }
}
