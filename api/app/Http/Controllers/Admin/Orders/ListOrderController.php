<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Store;
use App\Models\Wilaya;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListOrderController extends Controller
{
    /**
     * Display a paginated listing of orders with filtering.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $storeId = $request->query('store_id', 'all');
        $wilayaId = $request->query('wilaya_id', 'all');
        $deliveryMethod = $request->query('delivery_method', 'all');

        $query = Order::query()
            ->with([
                'orderStatus',
                'store',
                'user',
                'wilayaModel',
                'items.product.mainImage',
                'items.size',
            ]);

        // Filter by Order Status
        if ($status && $status !== 'all') {
            $query->where('order_status_code', $status);
        }

        // Filter by Store
        if ($storeId && $storeId !== 'all') {
            $query->where('store_id', $storeId);
        }

        // Filter by Wilaya
        if ($wilayaId && $wilayaId !== 'all') {
            $query->where('wilaya_id', $wilayaId);
        }

        // Filter by Delivery Method
        if ($deliveryMethod && $deliveryMethod !== 'all') {
            $query->where('delivery_method', $deliveryMethod);
        }

        // Search by order_number, customer name, phone, address, product name, or store name
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('wilaya', 'like', "%{$search}%")
                    ->orWhere('baladiya', 'like', "%{$search}%")
                    ->orWhere('home_address', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest('id')->paginate(10)->withQueryString();

        // Calculate count metrics for status tabs
        $statuses = OrderStatus::orderBy('sort_order')->get();
        $statusCounts = [
            'all' => Order::count(),
        ];
        foreach ($statuses as $st) {
            $statusCounts[$st->code] = Order::where('order_status_code', $st->code)->count();
        }

        $stores = Store::select('id', 'name', 'image_url')->get();
        $wilayas = Wilaya::select('id', 'number', 'code', 'en', 'fr', 'ar')->get();

        return Inertia::render('admin/orders/list.orders.page', [
            'orders' => $orders,
            'statuses' => $statuses,
            'statusCounts' => $statusCounts,
            'stores' => $stores,
            'wilayas' => $wilayas,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'store_id' => $storeId,
                'wilaya_id' => $wilayaId,
                'delivery_method' => $deliveryMethod,
            ],
        ]);
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status_code' => ['required', 'string', 'exists:order_statuses,code'],
        ]);

        $order->update([
            'order_status_code' => $validated['order_status_code'],
        ]);

        return back()->with('success', "Order #{$order->order_number} status updated successfully.");
    }
}
