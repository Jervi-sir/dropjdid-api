<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Resolve user from sanctum or X-User-Id header.
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
     * Get details of a single order by ID or order_number.
     */
    public function __invoke(Request $request, string|int $id): JsonResponse
    {
        $user = $this->resolveUser($request);

        $query = Order::query()
            ->with([
                'orderStatus',
                'items.product.mainImage',
                'items.product.images',
                'items.drop.mainImage',
                'items.drop.images',
                'items.size',
                'wilayaModel',
                'store',
            ]);

        // Find by primary key ID or order_number
        $order = is_numeric($id)
            ? $query->where('id', $id)->first()
            : $query->where('order_number', $id)->first();

        if (! $order) {
            // Try cleaning # from order_number if present
            $cleanedId = ltrim((string) $id, '#');
            $order = $query->where('order_number', $cleanedId)->first();
        }

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Determine image from first item
        $imageUrl = '';
        $firstItem = $order->items->first();
        if ($firstItem) {
            $product = $firstItem->product;
            $drop = $firstItem->drop;

            if ($product) {
                $imageUrl = $product->mainImage?->image_url
                    ?? $product->images->first()?->image_url
                    ?? '';
            } elseif ($drop) {
                $imageUrl = $drop->mainImage?->image
                    ?? $drop->images->first()?->image
                    ?? '';
            }
        }

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $statusCode = strtolower((string) ($order->order_status_code ?? $order->orderStatus?->code ?? 'pending'));
        
        $orderIdFormatted = $order->order_number
            ? (str_starts_with($order->order_number, '#') ? $order->order_number : '#' . $order->order_number)
            : '#' . $order->id;

        // Structured timeline steps based on order status
        $timeline = [
            [
                'step' => 1,
                'code' => 'pending',
                'title' => 'Waiting to confirm your order',
                'is_completed' => in_array($statusCode, ['confirmed', 'processing', 'shipped', 'delivered']),
                'is_current' => $statusCode === 'pending',
            ],
            [
                'step' => 2,
                'code' => 'shipped',
                'title' => 'Shipping started',
                'is_completed' => in_array($statusCode, ['delivered']),
                'is_current' => in_array($statusCode, ['confirmed', 'processing', 'shipped']),
            ],
            [
                'step' => 3,
                'code' => 'delivered',
                'title' => 'Delivered',
                'is_completed' => $statusCode === 'delivered',
                'is_current' => $statusCode === 'delivered',
            ],
        ];

        // Format items summary string e.g. "2 (S), 1 (M)"
        $sizesSummaryList = [];
        foreach ($order->items as $item) {
            $sizeCode = $item->size?->code ?? 'Standard';
            $sizesSummaryList[] = "{$item->quantity} ({$sizeCode})";
        }
        $sizesSummary = ! empty($sizesSummaryList) ? implode(', ', $sizesSummaryList) : '1 (Standard)';

        // Calculate refundable amount (subtotal - transaction fee/delivery)
        $refundableAmount = max(0, (float) $order->subtotal);

        $statusLabel = match ($statusCode) {
            'pending' => 'Waiting to confirm your order',
            'cancelled', 'canceled' => 'Canceled, waiting for automatique refund',
            'shipped' => 'Shipping started',
            'delivered', 'confirmed', 'processing' => 'Purchased successfuly',
            'refunded', 'returned' => 'Refunded successfuly',
            default => 'Waiting to confirm your order',
        };

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $order->id,
                'order_number' => (string) $order->order_number,
                'order_id' => (string) $orderIdFormatted,
                'image_url' => (string) $imageUrl,
                'status_code' => (string) $statusCode,
                'status_label' => (string) $statusLabel,
                'status_color' => (string) ($order->orderStatus?->color ?? '#8C94A3'),
                'timeline' => $timeline,
                
                // Customer & Shipping Info
                'full_name' => (string) $order->full_name,
                'phone_number' => (string) $order->phone_number,
                'wilaya' => (string) $order->wilaya,
                'baladiya' => (string) ($order->baladiya ?? ''),
                'home_address' => (string) ($order->home_address ?? ''),
                'delivery_method' => (string) ($order->delivery_method ?? 'home'),
                'delivery_label' => $order->delivery_method === 'desk' ? 'To delivery office (Stop Desk)' : 'Home delivery',

                // Financial details
                'sizes_summary' => (string) $sizesSummary,
                'subtotal' => (float) $order->subtotal,
                'delivery_fees' => (float) $order->delivery_fees,
                'transaction_fee' => 40.0,
                'total' => (float) $order->total,
                'refundable_amount' => (float) $refundableAmount,

                'has_claim_issue' => (bool) $order->has_claim_issue,
                'claim_issue' => $order->claim_issue ? (string) $order->claim_issue : null,
                'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
            ],
        ]);
    }
}
