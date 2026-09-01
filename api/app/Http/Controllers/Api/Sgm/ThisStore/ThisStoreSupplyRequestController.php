<?php

namespace App\Http\Controllers\Api\Sgm\ThisStore;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\SupplyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThisStoreSupplyRequestController extends Controller
{
    /**
     * Get list of supply requests for a specific store owned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $storeId = $request->query('store_id') ?? $request->query('storeId');

        if (! $storeId) {
            return response()->json(['message' => 'Store ID is required.'], 422);
        }

        // Verify the store belongs to the authenticated user
        $store = Store::where('id', $storeId)
            ->where('user_id', $user->id)
            ->first();

        if (! $store) {
            return response()->json(['message' => 'Store not found or unauthorized.'], 403);
        }

        $status = $request->query('status'); // Optional filter

        $query = SupplyRequest::query()
            ->where('store_id', $store->id)
            ->with([
                'items.product.mainImage',
                'items.size',
            ])
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $supplyRequests = $query->get()->map(function (SupplyRequest $sr) {
            return [
                'id' => $sr->id,
                'reference_code' => $sr->reference_code,
                'status' => $sr->status,
                'tracking_number' => $sr->tracking_number,
                'courier_name' => $sr->courier_name,
                'notes' => $sr->notes,
                'sent_at' => $sr->sent_at?->toISOString(),
                'shipped_at' => $sr->shipped_at?->toISOString(),
                'received_at' => $sr->received_at?->toISOString(),
                'completed_at' => $sr->completed_at?->toISOString(),
                'created_at' => $sr->created_at?->toISOString(),
                'total_requested_quantity' => $sr->items->sum('requested_quantity'),
                'total_fulfilled_quantity' => $sr->items->sum('fulfilled_quantity'),
                'total_received_quantity' => $sr->items->sum('received_quantity'),
                'items' => $sr->items->map(function ($item) {
                    $prod = $item->product;
                    $priceShown = $prod?->price_shown ?? $prod?->price_original ?? 0;
                    $priceOriginal = $prod?->price_original ?? 0;
                    $promoPercentage = '';
                    if ($priceOriginal && $priceShown && (float) $priceOriginal > (float) $priceShown) {
                        $discount = round(((float) $priceOriginal - (float) $priceShown) / (float) $priceOriginal * 100);
                        $promoPercentage = "{$discount}% OFF";
                    }

                    $imageUrl = $prod?->mainImage?->image_url ?? $item->product?->mainImage?->image_url ?? '';
                    if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                        $imageUrl = url($imageUrl);
                    }

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'image_url' => $imageUrl,
                        'imageUrl' => $imageUrl,
                        'price1' => $priceShown ? number_format((float) $priceShown, 0, '.', '') . ' DA' : '3800 DA',
                        'price2' => $priceOriginal && (float)$priceOriginal > (float)$priceShown ? number_format((float) $priceOriginal, 0, '.', '') : null,
                        'promoPercentage' => $promoPercentage ?: '27% OFF',
                        'description' => $prod?->description ?? 'First choice of many people.',
                        'size_code' => $item->size?->code ?? 'Standard',
                        'requested_quantity' => $item->requested_quantity,
                        'fulfilled_quantity' => $item->fulfilled_quantity,
                        'received_quantity' => $item->received_quantity,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $supplyRequests,
            'counts' => [
                'total' => $supplyRequests->count(),
                'pending' => $supplyRequests->whereIn('status', ['draft', 'sent', 'preparing'])->count(),
                'in_transit' => $supplyRequests->where('status', 'shipped_to_hub')->count(),
                'completed' => $supplyRequests->whereIn('status', ['received_at_hub', 'completed'])->count(),
            ],
        ]);
    }

    /**
     * Store updates supply request (e.g. confirms shipping and enters courier tracking info).
     */
    public function updateProgress(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $storeId = $request->input('store_id') ?? $request->input('storeId');

        $store = Store::where('id', $storeId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $supplyRequest = SupplyRequest::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:preparing,shipped_to_hub'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $update = ['status' => $validated['status']];
        if (isset($validated['courier_name'])) {
            $update['courier_name'] = $validated['courier_name'];
        }
        if (isset($validated['tracking_number'])) {
            $update['tracking_number'] = $validated['tracking_number'];
        }
        if ($validated['status'] === 'shipped_to_hub' && ! $supplyRequest->shipped_at) {
            $update['shipped_at'] = now();
        }

        $supplyRequest->update($update);

        return response()->json([
            'message' => 'Supply request updated successfully.',
            'data' => $supplyRequest,
        ]);
    }
}
