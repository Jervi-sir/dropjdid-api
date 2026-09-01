<?php

namespace App\Http\Controllers\Admin\SupplyRequests;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Services\SupplyAggregationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplyRequestController extends Controller
{
    public function __construct(
        protected SupplyAggregationService $aggregationService
    ) {}

    /**
     * Main supply requests dashboard:
     * - Tab 1: Supply Requests list (filterable by status)
     * - Tab 2: Pending Store Demands (ready to batch)
     * - Tab 3: Ready to Box Customer Orders
     */
    public function index(Request $request): Response
    {
        $tab = $request->query('tab', 'requests'); // 'requests', 'demands', 'ready_to_box'
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $storeId = $request->query('store_id', 'all');

        // 1. Supply Requests Query
        $requestsQuery = SupplyRequest::query()
            ->with(['store', 'items.product.mainImage', 'items.size'])
            ->withCount(['items', 'orderItems'])
            ->latest();

        if ($status !== 'all') {
            $requestsQuery->where('status', $status);
        }

        if ($storeId !== 'all') {
            $requestsQuery->where('store_id', $storeId);
        }

        if ($search !== '') {
            $requestsQuery->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('courier_name', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $supplyRequests = $requestsQuery->paginate(15)->withQueryString();

        // 2. Pending Demands Grouped By Store
        $pendingItems = OrderItem::where('fulfillment_status', 'awaiting_supply')
            ->whereNull('supply_request_id')
            ->with(['order', 'product.mainImage', 'product.store', 'size'])
            ->get();

        $storesWithDemands = $pendingItems->groupBy(function (OrderItem $item) {
            return $item->product?->store_id ?? $item->order?->store_id;
        })->map(function ($items, $storeIdKey) {
            $firstItem = $items->first();
            $store = $firstItem->product?->store ?? Store::find($storeIdKey);
            $orderIds = $items->pluck('order_id')->unique();

            // Group demands by Product, then breakdown by Size
            $productsGrouped = $items->groupBy('product_id')->map(function ($productItems) {
                $first = $productItems->first();

                $sizesBreakdown = $productItems->groupBy(function (OrderItem $pi) {
                    return $pi->size?->code ?? 'Standard';
                })->map(function ($sizeItems, $sizeCode) {
                    return [
                        'size' => $sizeCode,
                        'total_quantity' => $sizeItems->sum('quantity'),
                        'order_item_ids' => $sizeItems->pluck('id')->values()->all(),
                        'order_numbers' => $sizeItems->pluck('order.order_number')->filter()->unique()->values()->all(),
                    ];
                })->values();

                return [
                    'product_id' => $first->product_id,
                    'product_name' => $first->product_name ?? $first->product?->name ?? 'Product',
                    'image_url' => $first->product?->mainImage?->image_url,
                    'total_quantity' => $productItems->sum('quantity'),
                    'sizes' => $sizesBreakdown,
                    'order_item_ids' => $productItems->pluck('id')->values()->all(),
                ];
            })->values();

            return [
                'store' => $store,
                'total_items_count' => $items->sum('quantity'),
                'affected_orders_count' => $orderIds->count(),
                'products' => $productsGrouped,
                'order_items' => $items->map(function (OrderItem $oi) {
                    return [
                        'id' => $oi->id,
                        'order_id' => $oi->order_id,
                        'order_number' => $oi->order?->order_number,
                        'product_id' => $oi->product_id,
                        'product_name' => $oi->product_name,
                        'image_url' => $oi->product?->mainImage?->image_url,
                        'size' => $oi->size?->code,
                        'quantity' => $oi->quantity,
                        'created_at' => $oi->created_at?->toISOString(),
                    ];
                })->values(),
            ];
        })->values();

        // 3. Customer Orders Ready To Box
        $readyToBoxOrders = Order::query()
            ->whereHas('items')
            ->whereDoesntHave('items', function ($q) {
                $q->where('fulfillment_status', '!=', 'in_hub');
            })
            ->whereNotIn('order_status_code', ['delivered', 'cancelled', 'returned'])
            ->with(['orderStatus', 'store', 'items.product.mainImage', 'items.size'])
            ->latest()
            ->paginate(10, ['*'], 'ready_page')
            ->withQueryString();

        $stores = Store::select('id', 'name', 'image_url')->orderBy('name')->get();

        return Inertia::render('admin/supply-requests/index.supply-requests.page', [
            'supplyRequests' => $supplyRequests,
            'storesWithDemands' => $storesWithDemands,
            'readyToBoxOrders' => $readyToBoxOrders,
            'stores' => $stores,
            'filters' => [
                'tab' => $tab,
                'status' => $status,
                'search' => $search,
                'store_id' => $storeId,
            ],
        ]);
    }

    /**
     * Create and dispatch a new Supply Request batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'order_item_ids' => ['required', 'array', 'min:1'],
            'order_item_ids.*' => ['required', 'exists:order_items,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplyRequest = $this->aggregationService->createSupplyRequestForStore(
            (int) $validated['store_id'],
            $validated['order_item_ids'],
            $validated['notes'] ?? null
        );

        // Update status to sent immediately if created
        $supplyRequest->update([
            'status' => SupplyRequest::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('success', "Supply Request {$supplyRequest->reference_code} generated and sent to store.");
    }

    /**
     * Update supply request status, courier info, or hub receiving progress.
     */
    public function updateStatus(Request $request, SupplyRequest $supplyRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,preparing,shipped_to_hub,received_at_hub,completed,cancelled'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $updateData = ['status' => $validated['status']];

        if (isset($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }
        if (isset($validated['courier_name'])) {
            $updateData['courier_name'] = $validated['courier_name'];
        }
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        if ($validated['status'] === SupplyRequest::STATUS_SHIPPED_TO_HUB && ! $supplyRequest->shipped_at) {
            $updateData['shipped_at'] = now();
        }

        if ($validated['status'] === SupplyRequest::STATUS_COMPLETED && ! $supplyRequest->completed_at) {
            $updateData['completed_at'] = now();
        }

        $supplyRequest->update($updateData);

        return redirect()->back()->with('success', 'Supply request updated successfully.');
    }

    /**
     * Check in received quantities at Hub.
     */
    public function receiveItems(Request $request, SupplyRequest $supplyRequest): RedirectResponse
    {
        $validated = $request->validate([
            'received_items' => ['required', 'array'],
            'received_items.*.item_id' => ['required', 'exists:supply_request_items,id'],
            'received_items.*.received_quantity' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['received_items'] as $itemData) {
            $this->aggregationService->markSupplyItemReceivedAtHub(
                (int) $itemData['item_id'],
                (int) $itemData['received_quantity']
            );
        }

        return redirect()->back()->with('success', 'Hub item reception recorded successfully.');
    }
}
