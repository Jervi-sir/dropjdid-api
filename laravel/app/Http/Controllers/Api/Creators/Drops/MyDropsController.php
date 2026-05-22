<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyDropsController extends Controller
{
    public function listDrops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user->id;

        $drops = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->withCount('savedDrops')
            ->latest()
            ->paginate($perPage);

        Drop::loadFeedRelations($drops, $userId, function ($query): void {
            $query->withCount(['orderItems as order_items_sum_quantity' => function ($subQuery): void {
                $subQuery->whereColumn('order_items.drop_id', 'drop_product.drop_id');
            }]);
        });

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($user));

        $pendingBanner = Drop::query()
            ->where('creator_id', $userId)
            ->where('status', Drop::STATUS_DRAFT)
            ->get(['id', 'title'])
            ->map(fn (Drop $drop): array => [
                'drop_id' => $drop->id,
                'title' => $drop->title,
            ]);

        $rejectionBanner = Drop::query()
            ->where('creator_id', $userId)
            ->where('status', Drop::STATUS_REJECTED)
            ->get(['id', 'title'])
            ->map(fn (Drop $drop): array => [
                'drop_id' => $drop->id,
                'title' => $drop->title,
            ]);

        return response()->json([
            'data' => $formattedDrops,
            'total' => $drops->total(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
            'pending_banner' => $pendingBanner,
            'rejection_banner' => $rejectionBanner,
        ]);
    }

    public function products(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $drop = Drop::where('creator_id', $user->id)->findOrFail($drop_id);

        $products = $drop->products()
            ->withCount(['orderItems as order_items_sum_quantity' => function ($subQuery) use ($drop_id) {
                $subQuery->where('order_items.drop_id', $drop_id);
            }])
            ->with([
                'images',
                'store.user',
                'savedProducts' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => $drop->formatProduct($product, $user)),
            'total' => $products->total(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function show(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $drop = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->withCount('savedDrops')
            ->findOrFail($drop_id);

        Drop::loadFeedRelations($drop, $userId, function ($query): void {
            $query->withCount(['orderItems as order_items_sum_quantity' => function ($subQuery): void {
                $subQuery->whereColumn('order_items.drop_id', 'drop_product.drop_id');
            }]);
        });

        return response()->json($drop->formatDrop($user));
    }

    public function delete(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $drop = Drop::where('creator_id', $user->id)->findOrFail($drop_id);

        $drop->images()->delete();
        $drop->products()->detach();
        $drop->delete();

        return response()->json([
            'message' => 'Drop deleted successfully.',
        ]);
    }

    public function productOrders(Request $request, int $drop_id, int $product_id): JsonResponse
    {
        $user = $request->user();
        // Ensure the drop belongs to the authenticated creator
        $drop = Drop::where('creator_id', $user->id)->findOrFail($drop_id);

        // Fetch paginated orders of this product in this drop
        $orders = Order::query()
            ->whereHas('items', function ($query) use ($drop_id, $product_id) {
                $query->where('drop_id', $drop_id)->where('product_id', $product_id);
            })
            ->with(['user', 'paymentMethod', 'items' => function ($query) use ($drop_id, $product_id) {
                $query->where('drop_id', $drop_id)->where('product_id', $product_id)->with(['size', 'product.images']);
            }])
            ->latest()
            ->paginate($request->get('per_page', 10));

        $formattedOrders = collect($orders->items())->map(function (Order $order) {
            $item = $order->items->first();
            $customer = $order->user;
            $firstProduct = $item?->product;

            return [
                'id' => $order->id, // Order ID at top level
                'order_number' => $order->order_number,
                'type' => $order->paymentMethod?->code === 'online' ? 'online' : 'cod',
                'is_online' => $order->isOnline(),
                'status' => $order->formatStatusForMobile(),
                'image' => $firstProduct?->images->sortBy('sort_order')->first()?->image,
                'product_name' => $item?->product_name,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->toISOString(),
                'full_name' => $order->full_name,
                'phone_number' => $order->phone_number,
                'wilaya' => $order->wilaya,
                'baladiya' => $order->baladiya,
                'home_address' => $order->home_address,
                'delivery_method' => Order::DELIVERY_METHOD[$order->delivery_method] ?? 'home',
                'delivery_fees' => (float) $order->delivery_fees,
                'subtotal' => (float) $order->subtotal,

                // Item details
                'quantity' => $item?->quantity ?? 0,
                'unit_price' => $item ? (float) $item->unit_price : 0.0,
                'total_price' => $item ? (float) $item->total_price : 0.0,
                'size' => $item?->size?->code ?? $item?->size?->en ?? $item?->size?->fr ?? $item?->size?->ar,

                // Customer/Buyer details
                'user' => [
                    'id' => $customer?->id,
                    'full_name' => $customer?->full_name ?? $order->full_name ?? 'Anonymous',
                    'username' => $customer?->username ?? 'unknown',
                    'image' => $customer?->image,
                    'phone_number' => $order->phone_number ?? $customer?->phone_number,
                ],
            ];
        });

        return response()->json([
            'data' => $formattedOrders,
            'total' => $orders->total(),
            'next_page' => $orders->hasMorePages() ? $orders->currentPage() + 1 : null,
        ]);
    }
}
