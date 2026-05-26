<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderController extends Controller
{
    public function __invoke(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'wilaya' => 'required|string|max:255',
            'wilaya_id' => 'required|integer|exists:wilayas,id',
            'baladiya' => 'nullable|string|max:255',
            'home_address' => 'required|string|max:500',
            'delivery_method' => 'required|string|in:home,desk',
            'delivery_fees' => 'required|integer',
            'selected_sizes' => 'required|array|min:1',
            'selected_sizes.*' => 'integer|min:1',
        ]);

        $product = Product::with('store')->find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return DB::transaction(function () use ($request, $product) {
            $subtotal = 0;
            $items = [];

            // Calculate subtotal and prepare items
            foreach ($request->selected_sizes as $sizeId => $quantity) {
                // In a real app, you might want to verify if the size exists and belongs to the product
                // and if the price is different per size. For now, we use the product price.
                $unitPrice = (int) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0);
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'size_id' => $sizeId,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $deliveryFees = (int) $request->delivery_fees;
            $otherFees = 40; // Fixed transaction fees as per frontend
            $total = $subtotal + $deliveryFees + $otherFees;

            $deliveryMethod = match ($request->delivery_method) {
                'desk' => Order::DELIVERY_METHOD_DESK,
                default => Order::DELIVERY_METHOD_HOME,
            };

            $status = Order::STATUS_PENDING;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'store_id' => $product->store_id,
                'wilaya_id' => $request->wilaya_id,
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'payment_method_id' => $product->payment_method_id,
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'wilaya' => $request->wilaya,
                'baladiya' => $request->baladiya ?? 'Alger',
                'home_address' => $request->home_address,
                'delivery_method' => $deliveryMethod,
                'delivery_fees' => $deliveryFees,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => $status,
            ]);

            foreach ($items as $itemData) {
                $order->items()->create($itemData);
            }

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order->load('items.product'),
            ], 201);
        });
    }
}
