<?php

namespace App\Http\Controllers\Admin\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListStoresController extends Controller
{
    /**
     * Display a listing of the stores with filters, search, and pagination.
     */
    public function __invoke(Request $request): Response
    {
        $query = Store::query()
            ->with(['user', 'wilaya'])
            ->withCount(['products', 'orders']);

        // Apply search filter (by store name, phone, or owner full name/username)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('username', 'like', '%'.$search.'%');
                    });
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $statusInput = $request->input('status');

            if (is_string($statusInput) && ! is_numeric($statusInput)) {
                $statusMap = array_flip(Store::STATUSES);
                $statusVal = $statusMap[$statusInput] ?? null;
            } else {
                $statusVal = (int) $statusInput;
            }

            if ($statusVal !== null) {
                $query->where('status', $statusVal);
            }
        }

        // Paginate results (default 10 per page)
        $perPage = $request->input('per_page', 10);
        $stores = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format paginated stores for Inertia response
        $formattedStores = $stores->through(function (Store $store) {
            return [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'logo' => $store->logo,
                'description' => $store->description,
                'balance' => (float) $store->balance,
                'status' => Store::STATUSES[$store->status] ?? 'unknown',
                'is_verified' => (bool) $store->is_verified,
                'user' => $store->user ? [
                    'id' => $store->user->id,
                    'full_name' => $store->user->full_name,
                    'username' => $store->user->username,
                ] : null,
                'wilaya' => $store->wilaya ? [
                    'id' => $store->wilaya->id,
                    'name' => $store->wilaya->name,
                ] : null,
                'products_count' => $store->products_count,
                'orders_count' => $store->orders_count,
                'created_at' => $store->created_at?->toIso8601String(),
            ];
        });

        // Compute high-level stats for the dashboard counters
        $stats = [
            'total' => Store::count(),
            'pending' => Store::where('status', Store::STATUS_PENDING)->count(),
            'active' => Store::where('status', Store::STATUS_ACTIVE)->count(),
            'suspended' => Store::where('status', Store::STATUS_SUSPENED)->count(),
            'verified' => Store::where('is_verified', true)->count(),
        ];

        return Inertia::render('admin/stores/list', [
            'stores' => $formattedStores,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'per_page' => (int) $perPage,
            ],
            'statuses' => Store::STATUSES,
            'stats' => $stats,
        ]);
    }
}
