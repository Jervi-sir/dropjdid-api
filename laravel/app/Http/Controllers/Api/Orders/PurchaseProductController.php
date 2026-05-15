<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseProductController extends Controller
{
    public function getProductInfo(Request $request, int $productId): JsonResponse
    {
        $product = Product::with(['images', 'quality', 'category', 'variants.size', 'paymentMethod'])->find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'price' => (int) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'quality' => [
                    'id' => $product->quality?->id,
                    'code' => $product->quality?->code,
                    'en' => $product->quality?->en,
                    'fr' => $product->quality?->fr,
                    'ar' => $product->quality?->ar,
                ],
                'category' => [
                    'id' => $product->category?->id,
                    'code' => $product->category?->code,
                    'en' => $product->category?->en,
                    'fr' => $product->category?->fr,
                    'ar' => $product->category?->ar,
                ],
                'payment_method' => $product->paymentMethod ? [
                    'id' => $product->paymentMethod->id,
                    'code' => $product->paymentMethod->code,
                    'en' => $product->paymentMethod->en,
                    'fr' => $product->paymentMethod->fr,
                    'ar' => $product->paymentMethod->ar,
                ] : null,
                'available_sizes' => $product->variants
                    ->map(fn ($variant) => [
                        'id' => $variant->size?->id,
                        'code' => $variant->size?->code,
                        'en' => $variant->size?->en,
                        'fr' => $variant->size?->fr,
                        'ar' => $variant->size?->ar,
                    ])
                    ->filter(fn ($size) => $size['id'] !== null)
                    ->unique('id')
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function purchase(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'wilaya' => 'required|string',
            'baladiya' => 'required|string',
            'home_address' => 'required|string',
            'delivery_method' => 'required|in:home,desk',
            'selected_sizes' => 'required|array',
            'selected_sizes.*' => 'integer|min:1',
        ]);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return DB::transaction(function () use ($request, $product) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->selected_sizes as $sizeId => $quantity) {
                $unitPrice = (int) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0);
                $totalPrice = (int) ($unitPrice * $quantity);
                $subtotal += $totalPrice;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'size_id' => $sizeId,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $deliveryFees = 350; 
            $otherFees = 40;
            $total = $subtotal + $deliveryFees + $otherFees;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'store_id' => $product->store_id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'payment_method_id' => $product->payment_method_id ?? 1, 
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'wilaya' => $request->wilaya,
                'baladiya' => $request->baladiya,
                'home_address' => $request->home_address,
                'delivery_method' => $request->delivery_method,
                'delivery_fees' => $deliveryFees,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'data' => $order->load('items')
            ], 201);
        });
    }
}
