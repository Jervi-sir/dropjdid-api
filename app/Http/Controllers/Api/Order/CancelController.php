<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelController extends Controller
{
    /**
     * Resolve user from sanctum or request headers/params.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (! $user) {
            $userId = $request->input('user_id') ?? $request->header('X-User-Id') ?? $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }
        return $user;
    }

    /**
     * Cancel an order if it is in a cancellable status (pending, confirmed, processing).
     */
    public function __invoke(Request $request, string|int $id): JsonResponse
    {
        $user = $this->resolveUser($request);

        $query = Order::query()->with(['orderStatus', 'items']);

        $order = is_numeric($id)
            ? $query->where('id', $id)->first()
            : $query->where('order_number', $id)->first();

        if (! $order) {
            $cleanedId = ltrim((string) $id, '#');
            $order = $query->where('order_number', $cleanedId)->first();
        }

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $currentStatus = strtolower((string) ($order->order_status_code ?? 'pending'));

        // Prevent cancelling already shipped, delivered, or already cancelled orders
        if (in_array($currentStatus, ['shipped', 'delivered'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled because it has already been shipped or delivered.',
            ], 422);
        }

        if ($currentStatus === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This order is already cancelled.',
            ], 422);
        }

        // Update order status to cancelled
        $order->order_status_code = OrderStatus::CANCELLED;
        $order->save();

        // Calculate refundable amount
        $refundableAmount = max(0, (float) $order->subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Order has been successfully cancelled. Your automatic refund is being processed.',
            'data' => [
                'id' => (int) $order->id,
                'order_number' => (string) $order->order_number,
                'order_status_code' => OrderStatus::CANCELLED,
                'status_label' => 'Cancelled',
                'refundable_amount' => (float) $refundableAmount,
            ],
        ]);
    }
}
