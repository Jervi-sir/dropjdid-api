<?php

namespace App\Http\Controllers\Admin\Wallets;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListWalletsController extends Controller
{
    /**
     * Display a listing of the wallets with search, filters, and KPI summary stats.
     */
    public function __invoke(Request $request): Response
    {
        $query = Wallet::query()->with(['user']);

        // Aggregate high-level KPI Metrics
        $totalBalance = Wallet::sum('balance');
        $totalPending = Wallet::sum('pending_balance');
        $verifiedCount = Wallet::where('is_identity_verified', true)->count();
        $blockedCount = Wallet::where('status', Wallet::STATUS_BLOCKED)->count();

        // 1. Apply search filter (owner name, username, email)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
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
        $formattedWallets = $wallets->through(function (Wallet $wallet) {
            return [
                'id' => $wallet->id,
                'user' => $wallet->user ? [
                    'id' => $wallet->user->id,
                    'full_name' => $wallet->user->full_name,
                    'username' => $wallet->user->username,
                    'email' => $wallet->user->email,
                    'image' => $wallet->user->image,
                ] : null,
                'type' => Wallet::TYPES[$wallet->type] ?? 'unknown',
                'type_raw' => $wallet->type,
                'balance' => $wallet->balance,
                'pending_balance' => $wallet->pending_balance,
                'is_identity_verified' => $wallet->is_identity_verified,
                'status' => Wallet::STATUSES[$wallet->status] ?? 'unknown',
                'status_raw' => $wallet->status,
                'currency' => $wallet->currency ?? 'DZD',
                'created_at' => $wallet->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('admin/wallets/list', [
            'wallets' => $formattedWallets,
            'kpis' => [
                'total_balance' => number_format($totalBalance, 2, '.', ''),
                'total_pending_balance' => number_format($totalPending, 2, '.', ''),
                'verified_identity_count' => $verifiedCount,
                'blocked_count' => $blockedCount,
                'total_count' => Wallet::count(),
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
