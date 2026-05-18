<?php

namespace App\Http\Controllers\Admin\StoreWallets;

use App\Http\Controllers\Controller;
use App\Models\StoreWallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListStoreWalletsController extends Controller
{
    /**
     * Display a listing of the store wallets with search, filters, and KPI summary stats.
     */
    public function __invoke(Request $request): Response
    {
        $query = StoreWallet::query()->with(['store']);

        // Aggregate high-level KPI Metrics
        $totalBalance = StoreWallet::sum('balance');
        $totalPending = StoreWallet::sum('pending_balance');
        $verifiedCount = StoreWallet::where('is_identity_verified', true)->count();
        $blockedCount = StoreWallet::where('status', StoreWallet::STATUS_BLOCKED)->count();

        // 1. Apply search filter (store name, phone number)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('store', function ($q) use ($search) {
                $q->where('store_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%');
            });
        }

        // 2. Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        // 3. Apply type filter
        if ($request->has('type') && $request->input('type') !== '') {
            $query->where('type', $request->input('type'));
        }

        // 4. Apply identity verification filter
        if ($request->has('is_identity_verified') && $request->input('is_identity_verified') !== '') {
            $query->where('is_identity_verified', $request->boolean('is_identity_verified'));
        }

        $perPage = $request->input('per_page', 15);
        $wallets = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format wallets list for Inertia view
        $formattedWallets = $wallets->through(function (StoreWallet $wallet) {
            return [
                'id' => $wallet->id,
                'store' => $wallet->store ? [
                    'id' => $wallet->store->id,
                    'store_name' => $wallet->store->store_name,
                    'phone_number' => $wallet->store->phone_number,
                    'logo' => $wallet->store->logo,
                ] : null,
                'type' => StoreWallet::TYPES[$wallet->type] ?? 'unknown',
                'type_raw' => $wallet->type,
                'balance' => $wallet->balance,
                'pending_balance' => $wallet->pending_balance,
                'is_identity_verified' => $wallet->is_identity_verified,
                'status' => StoreWallet::STATUSES[$wallet->status] ?? 'unknown',
                'status_raw' => $wallet->status,
                'currency' => $wallet->currency ?? 'DZD',
                'created_at' => $wallet->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('admin/store-wallets/list', [
            'wallets' => $formattedWallets,
            'kpis' => [
                'total_balance' => number_format($totalBalance, 2, '.', ''),
                'total_pending_balance' => number_format($totalPending, 2, '.', ''),
                'verified_identity_count' => $verifiedCount,
                'blocked_count' => $blockedCount,
                'total_count' => StoreWallet::count(),
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'type' => $request->input('type', ''),
                'is_identity_verified' => $request->input('is_identity_verified', ''),
                'per_page' => (int) $perPage,
            ],
        ]);
    }
}
