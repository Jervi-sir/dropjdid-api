<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListOrdersByProductsController extends Controller
{
    public function listProducts(Request $request, int $storeId): JsonResponse
    {
        $request->validate([
            'is_online' => ['required', 'boolean'],
        ]);

        $isOnline = $request->boolean('is_online');
        $user = $request->user();

        $statuses = [
            Order::STATUS_PENDING => 'pending',
            Order::STATUS_CONFIRMED => 'confirmed',
            Order::STATUS_PROCESSING => 'processing',
            Order::STATUS_SHIPPED => 'shipped',
            Order::STATUS_DELIVERED => 'delivered',
            Order::STATUS_CANCELLED => 'cancelled',
            Order::STATUS_RETURNED => 'returned',
        ];

        $groupedData = [];

        foreach ($statuses as $statusCode => $statusName) {
            $products = Product::where('store_id', $storeId)
                ->whereHas('orderItems', function ($query) use ($isOnline, $statusCode) {
                    $query->whereHas('order', function ($orderQuery) use ($isOnline, $statusCode) {
                        $orderQuery->whereHas('paymentMethod', function ($pmQuery) use ($isOnline) {
                            $pmQuery->where('is_online', $isOnline);
                        })->where('status', $statusCode);
                    });
                })
                ->withCount(['orderItems as total_orders' => function ($query) use ($isOnline, $statusCode) {
                    $query->whereHas('order', function ($orderQuery) use ($isOnline, $statusCode) {
                        $orderQuery->whereHas('paymentMethod', function ($pmQuery) use ($isOnline) {
                            $pmQuery->where('is_online', $isOnline);
                        })->where('status', $statusCode);
                    });
                }])
                ->with(['images', 'store.user', 'paymentMethod', 'savedProducts'])
                ->get();

            $formattedProducts = $products->map(function ($product) use ($user) {
                $formatted = $product->formatProduct($product, $user);
                $formatted['total_orders'] = $product->total_orders;
                $formatted['status'] = $product->status == Product::STATUS_PUBLISHED ? 'available' : 'out of stock';

                return $formatted;
            });

            $groupedData[] = [
                'status' => $statusName,
                'status_code' => $statusCode,
                'products' => $formattedProducts,
            ];
        }

        return response()->json([
            'data' => $groupedData,
        ]);
    }

    public function listProductOrders(Request $request, int $storeId, int $product_id): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
        ]);

        $status = $request->has('status') && $request->input('status') !== null && $request->input('status') !== ''
            ? (int) $request->input('status')
            : null;

        $orders = Order::where('store_id', $storeId)
            ->whereHas('items', function ($query) use ($product_id, $storeId) {
                $query->where('product_id', $product_id)
                    ->whereHas('product', function ($productQuery) use ($storeId) {
                        $productQuery->where('store_id', $storeId);
                    });
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->with(['items.product.images', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $formattedOrders = $orders->map(function ($order) {
            return $order->formatForList();
        });

        return response()->json([
            'data' => $formattedOrders,
            'next_page' => $orders->hasMorePages() ? $orders->currentPage() + 1 : null,
        ]);
    }
}
