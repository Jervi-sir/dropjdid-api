<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
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
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->withSum(['orderItems as order_items_sum_quantity' => function ($subQuery) {
                        $subQuery->whereColumn('order_items.drop_id', 'drop_product.drop_id');
                    }], 'quantity')->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
            ])
            ->latest()
            ->paginate($perPage);

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
            ->withSum(['orderItems as order_items_sum_quantity' => function ($subQuery) use ($drop_id) {
                $subQuery->where('order_items.drop_id', $drop_id);
            }], 'quantity')
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
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->withSum(['orderItems as order_items_sum_quantity' => function ($subQuery) {
                        $subQuery->whereColumn('order_items.drop_id', 'drop_product.drop_id');
                    }], 'quantity')->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
            ])
            ->findOrFail($drop_id);

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

        // Fetch paginated order items of this product in this drop
        $orderItems = OrderItem::query()
            ->where('drop_id', $drop_id)
            ->where('product_id', $product_id)
            ->with(['order.user', 'size'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        $formattedOrders = collect($orderItems->items())->map(function (OrderItem $item) {
            $order = $item->order;
            $customer = $order?->user;

            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'order_number' => $order?->order_number,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'size' => $item->size?->code ?? $item->size?->en ?? $item->size?->fr ?? $item->size?->ar,
                'status' => $order?->status !== null ? (Order::STATUS[$order->status] ?? 'pending') : 'pending',
                'created_at' => $item->created_at?->toISOString(),
                'user' => [
                    'id' => $customer?->id,
                    'full_name' => $customer?->full_name ?? $order?->full_name ?? 'Anonymous',
                    'username' => $customer?->username ?? 'unknown',
                    'image' => $customer?->image,
                    'phone_number' => $order?->phone_number ?? $customer?->phone_number,
                ],
            ];
        });

        return response()->json([
            'data' => $formattedOrders,
            'total' => $orderItems->total(),
            'next_page' => $orderItems->hasMorePages() ? $orderItems->currentPage() + 1 : null,
        ]);
    }
}
