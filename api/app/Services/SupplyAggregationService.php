<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Store;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplyAggregationService
{
    /**
     * Get all pending unassigned order items grouped by store.
     */
    public function getPendingItemsGroupedByStore(): Collection
    {
        return OrderItem::where('fulfillment_status', 'awaiting_supply')
            ->whereNull('supply_request_id')
            ->with(['order', 'product.store', 'size', 'drop'])
            ->get()
            ->groupBy(function (OrderItem $item) {
                return $item->product?->store_id ?? $item->order?->store_id;
            });
    }

    /**
     * Create a supply request draft for a specific store from a collection of order item IDs.
     *
     * @param int $storeId
     * @param array<int> $orderItemIds
     * @param string|null $notes
     * @return SupplyRequest
     */
    public function createSupplyRequestForStore(int $storeId, array $orderItemIds, ?string $notes = null): SupplyRequest
    {
        return DB::transaction(function () use ($storeId, $orderItemIds, $notes) {
            $store = Store::findOrFail($storeId);

            // 1. Fetch the target unassigned order items
            $orderItems = OrderItem::whereIn('id', $orderItemIds)
                ->where('fulfillment_status', 'awaiting_supply')
                ->whereNull('supply_request_id')
                ->with(['product', 'size', 'drop'])
                ->get();

            if ($orderItems->isEmpty()) {
                throw new \InvalidArgumentException('No valid pending order items found for supply request.');
            }

            // 2. Generate Unique Reference Code (e.g. SR-20260830-AB12)
            $refCode = 'SR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // 3. Create parent Supply Request
            $supplyRequest = SupplyRequest::create([
                'reference_code' => $refCode,
                'store_id' => $store->id,
                'status' => SupplyRequest::STATUS_DRAFT,
                'notes' => $notes,
            ]);

            // 4. Group items by product_id + size_id + drop_id to calculate aggregated batch quantities
            $groupedByVariant = $orderItems->groupBy(function (OrderItem $item) {
                return "{$item->product_id}_{$item->size_id}_{$item->drop_id}";
            });

            foreach ($groupedByVariant as $group) {
                /** @var Collection<int, OrderItem> $group */
                $first = $group->first();
                $totalQuantity = $group->sum('quantity');

                // Create batched supply request line item
                $supplyRequestItem = SupplyRequestItem::create([
                    'supply_request_id' => $supplyRequest->id,
                    'product_id' => $first->product_id,
                    'size_id' => $first->size_id,
                    'drop_id' => $first->drop_id,
                    'product_name' => $first->product_name ?? $first->product?->name ?? 'Product',
                    'requested_quantity' => $totalQuantity,
                    'fulfilled_quantity' => 0,
                    'received_quantity' => 0,
                ]);

                // 5. Link individual order_items to this supply request and item
                foreach ($group as $orderItem) {
                    $orderItem->update([
                        'supply_request_id' => $supplyRequest->id,
                        'supply_request_item_id' => $supplyRequestItem->id,
                        'fulfillment_status' => 'supply_requested',
                    ]);
                }
            }

            return $supplyRequest->load(['items.product', 'items.size', 'orderItems.order']);
        });
    }

    /**
     * Mark items as received at hub when store shipment arrives, and update linked order items.
     *
     * @param int $supplyRequestItemId
     * @param int $quantityReceived
     */
    public function markSupplyItemReceivedAtHub(int $supplyRequestItemId, int $quantityReceived): SupplyRequestItem
    {
        return DB::transaction(function () use ($supplyRequestItemId, $quantityReceived) {
            $item = SupplyRequestItem::with(['orderItems', 'supplyRequest'])->findOrFail($supplyRequestItemId);

            $item->received_quantity = min($item->requested_quantity, $item->received_quantity + $quantityReceived);
            $item->save();

            // Progress linked order items to 'in_hub' up to the received quantity
            $remaining = $quantityReceived;
            foreach ($item->orderItems as $orderItem) {
                if ($remaining <= 0) {
                    break;
                }
                if ($orderItem->fulfillment_status !== 'in_hub') {
                    $orderItem->update(['fulfillment_status' => 'in_hub']);
                    $remaining -= $orderItem->quantity;
                }
            }

            // Check if all items in this supply request are fully received
            $parent = $item->supplyRequest;
            $allReceived = $parent->items()->whereRaw('received_quantity < requested_quantity')->doesntExist();
            if ($allReceived) {
                $parent->update([
                    'status' => SupplyRequest::STATUS_RECEIVED_AT_HUB,
                    'received_at' => now(),
                ]);
            }

            return $item;
        });
    }
}
