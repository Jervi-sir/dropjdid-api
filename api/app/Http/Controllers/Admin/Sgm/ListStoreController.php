<?php

namespace App\Http\Controllers\Admin\Sgm;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Store;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListStoreController extends Controller
{
    /**
     * Display a paginated listing of stores.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = Store::query()->with(['user.roles', 'wilaya']);

        // Filter by status if specified
        if ($status && $status !== 'all') {
            if ($status === 'pending') {
                $query->where(function ($q) {
                    $q->where('store_status', 'pending')
                      ->orWhere('is_approved', false);
                });
            } else {
                $query->where('store_status', $status);
            }
        }

        // Search by store name, phone number, or owner info
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $stores = $query->latest('id')->paginate(10)->withQueryString();

        // Calculate count metrics for tab badges
        $counts = [
            'all' => Store::count(),
            'pending' => Store::where('store_status', 'pending')->orWhere('is_approved', false)->count(),
            'active' => Store::where('store_status', 'active')->where('is_approved', true)->count(),
            'suspended' => Store::where('store_status', 'suspended')->count(),
        ];

        return Inertia::render('admin/sgms/list.stores', [
            'stores' => $stores,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Approve a store and set active status.
     */
    public function approve(Request $request, Store $store): RedirectResponse
    {
        $store->update([
            'is_approved' => true,
            'store_status' => Store::STATUS_ACTIVE,
        ]);

        if ($store->user_id) {
            $sgmRole = Role::firstOrCreate(
                ['code' => Role::SGM],
                [
                    'en' => 'Store General Manager',
                    'fr' => 'Gérant de magasin',
                    'ar' => 'مدير متجر',
                ]
            );

            UserRole::firstOrCreate([
                'user_id' => $store->user_id,
                'role_id' => $sgmRole->id,
            ]);
        }

        return back()->with('success', 'Store approved and activated successfully.');
    }

    /**
     * Update store status (active, suspended, pending) or approval.
     */
    public function updateStatus(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'store_status' => ['required', 'string', 'in:pending,active,suspended'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $status = $validated['store_status'];
        $isApproved = $request->has('is_approved')
            ? (bool) $request->input('is_approved')
            : ($status === Store::STATUS_ACTIVE);

        $store->update([
            'store_status' => $status,
            'is_approved' => $isApproved,
        ]);

        return back()->with('success', "Store status updated to {$status}.");
    }
}
