<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Drop;
use App\Models\Friendship;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreWallet;
use App\Models\StoreWithdrawalRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display a comprehensive overview of system interactions, shopper metrics, store queues, and merchant payouts.
     */
    public function __invoke(Request $request): Response
    {
        // 1. Shopper Metrics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        // 2. Merchant Store Profiles
        $totalStores = Store::count();
        $pendingStores = Store::where('status', 0)->count(); // 0 represents new/pending store

        // 3. Product Catalog Audit Queue
        $totalProducts = Product::count();
        $pendingProducts = Product::where('status', Product::STATUS_DRAFT)->count();

        // 4. Publishing Drops & Distribution
        $totalDrops = Drop::count();

        // 5. Total Ledgers held in Wallets
        $totalUserWalletBalance = Wallet::sum('balance');
        $totalStoreWalletBalance = StoreWallet::sum('balance');

        // 6. Actionable Withdrawal Queues
        $pendingUserWithdrawals = WithdrawalRequest::whereIn('status', [
            WithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK,
            WithdrawalRequest::STATUS_PENDING,
        ])->count();

        $pendingStoreWithdrawals = StoreWithdrawalRequest::whereIn('status', [
            StoreWithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK,
            StoreWithdrawalRequest::STATUS_PENDING,
        ])->count();

        // 7. Peer-to-Peer Interactivity
        $totalFriendships = Friendship::where('status', 1)->count(); // accepted friendships
        $totalConversations = Conversation::count();

        // 8. Actionable Tables: Stores awaiting registration audit
        $recentPendingStores = Store::where('status', 0)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($store) => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'created_at' => $store->created_at?->toIso8601String(),
                'owner' => $store->user ? [
                    'id' => $store->user->id,
                    'full_name' => $store->user->full_name,
                    'email' => $store->user->email,
                ] : null,
            ]);

        // 9. Actionable Tables: Recent merchant withdrawal payout requests
        $recentStoreWithdrawals = StoreWithdrawalRequest::whereIn('status', [
            StoreWithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK,
            StoreWithdrawalRequest::STATUS_PENDING,
        ])
            ->with(['store'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'store_id' => $req->store_id,
                'store_name' => $req->store?->store_name,
                'amount' => (float) $req->amount,
                'method' => $req->method,
                'status' => $req->status,
                'created_at' => $req->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/admin-dashboard', [
            'stats' => [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                ],
                'stores' => [
                    'total' => $totalStores,
                    'pending' => $pendingStores,
                ],
                'products' => [
                    'total' => $totalProducts,
                    'pending' => $pendingProducts,
                ],
                'drops' => [
                    'total' => $totalDrops,
                ],
                'finances' => [
                    'total_user_balance' => (float) $totalUserWalletBalance,
                    'total_store_balance' => (float) $totalStoreWalletBalance,
                    'pending_user_withdrawals' => $pendingUserWithdrawals,
                    'pending_store_withdrawals' => $pendingStoreWithdrawals,
                ],
                'social' => [
                    'friendships' => $totalFriendships,
                    'conversations' => $totalConversations,
                ],
            ],
            'recentPendingStores' => $recentPendingStores,
            'recentStoreWithdrawals' => $recentStoreWithdrawals,
        ]);
    }
}
