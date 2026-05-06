<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'paymentMethod',
                'items.product.images',
            ])
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $orders->getCollection()
                ->map(fn (Order $order): array => $order->formatForList())
                ->values(),
            'next_page' => $orders->hasMorePages() ? $orders->currentPage() + 1 : null,
        ]);
    }
}
