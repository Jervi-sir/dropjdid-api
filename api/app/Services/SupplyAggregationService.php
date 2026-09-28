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
     * Create a supply request for a specific product from a collection of order item IDs.
     *
     * @param int $productId
     * @param array<int> $orderItemIds
     * @param string|null $notes
     * @param bool $autoApprove
     * @return SupplyRequest
     */
    public function createSupplyRequestForProduct(int $productId, array $orderItemIds, ?string $notes = null, bool $autoApprove = true): SupplyRequest
    {
        return DB::transaction(function () use ($productId, $orderItemIds, $notes, $autoApprove) {
            // 1. Fetch target unassigned order items for this product
            $orderItems = OrderItem::whereIn('id', $orderItemIds)
                ->where('product_id', $productId)
                ->where('fulfillment_status', 'awaiting_supply')
                ->whereNull('supply_request_id')
                ->with(['product.store', 'size', 'drop'])
                ->get();

            if ($orderItems->isEmpty()) {
                throw new \InvalidArgumentException('No valid pending order items found for this product.');
            }

            $firstItem = $orderItems->first();
            $storeId = $firstItem->product?->store_id ?? $firstItem->order?->store_id ?? 1;
            $productName = (string) ($firstItem->product_name ?? $firstItem->product?->name ?? "Product #{$productId}");
            $totalQuantity = (int) $orderItems->sum('quantity');

            // 2. Generate Unique Reference Code (e.g. SR-20260925-AB12)
            $refCode = 'SR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $initialStatus = $autoApprove ? SupplyRequest::STATUS_APPROVED : SupplyRequest::STATUS_DRAFT;

            // 3. Create product-specific Supply Request
            $supplyRequest = SupplyRequest::create([
                'reference_code' => $refCode,
                'product_id' => $productId,
                'product_name' => $productName,
                'total_requested_quantity' => $totalQuantity,
                'total_fulfilled_quantity' => 0,
                'total_received_quantity' => 0,
                'status' => $initialStatus,
                'approved_at' => $autoApprove ? now() : null,
                'sent_at' => $autoApprove ? now() : null,
                'notes' => $notes,
            ]);

            // 4. Group items by size & drop to create size-breakdown line items
            $groupedByVariant = $orderItems->groupBy(function (OrderItem $item) {
                return "{$item->size_id}_{$item->drop_id}";
            });

            foreach ($groupedByVariant as $group) {
                /** @var Collection<int, OrderItem> $group */
                $first = $group->first();
                $variantQuantity = (int) $group->sum('quantity');
                $sizeCode = (string) ($first->size?->code ?? $first->size?->en ?? 'Standard');

                // Create size breakdown item
                $supplyRequestItem = SupplyRequestItem::create([
                    'supply_request_id' => $supplyRequest->id,
                    'product_id' => $productId,
                    'size_id' => $first->size_id,
                    'size_code' => $sizeCode,
                    'drop_id' => $first->drop_id,
                    'product_name' => $productName,
                    'requested_quantity' => $variantQuantity,
                    'fulfilled_quantity' => 0,
                    'received_quantity' => 0,
                ]);

                // 5. Link individual order_items to this supply request and item
                foreach ($group as $orderItem) {
                    $orderItem->update([
                        'supply_request_id' => $supplyRequest->id,
                        'supply_request_item_id' => $supplyRequestItem->id,
                        'fulfillment_status' => $autoApprove ? 'supply_requested' : 'awaiting_supply',
                    ]);
                }
            }

            return $supplyRequest->load(['product.mainImage', 'store', 'items.size', 'orderItems.order']);
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
