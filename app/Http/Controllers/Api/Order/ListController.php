<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * Get paginated orders matching OrderType interface:
     * - id: number
     * - order_id: string
     * - image_url: string
     * - status: { code: string, label: string }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $query = Order::query()
            ->with([
                'items' => function ($q) {
                    $q->with([
                        'product.mainImage',
                        'product.images',
                        'drop.mainImage',
                        'drop.images',
                    ]);
                },
            ])
            ->latest('created_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Order $order) {
            return $this->formatOrder($order);
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $nextPage,
        ], 200);
    }

    /**
     * Format Order model instance into OrderType structure.
     *
     * @param Order $order
     * @return array
     */
    protected function formatOrder(Order $order): array
    {
        $firstItem = $order->items->first();
        $imageUrl = '';

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
        $statusLabel = match ($statusCode) {
            'pending' => 'Waiting to confirm your order',
            'cancelled', 'canceled' => 'Canceled, waiting for automatique refund',
            'shipped' => 'Shipping started',
            'delivered', 'confirmed', 'processing' => 'Purchased successfuly',
            'refunded', 'returned' => 'Refunded successfuly',
            default => 'Waiting to confirm your order',
        };

        $orderIdFormatted = $order->order_number
            ? (str_starts_with($order->order_number, '#') ? $order->order_number : '#' . $order->order_number)
            : '#' . $order->id;

        return [
            'id' => (int) $order->id,
            'order_id' => (string) $orderIdFormatted,
            'image_url' => (string) $imageUrl,
            'status' => [
                'code' => (string) $statusCode,
                'label' => (string) $statusLabel,
            ],
        ];
    }
}
