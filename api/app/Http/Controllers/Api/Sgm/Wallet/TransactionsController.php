<?php

namespace App\Http\Controllers\Api\Sgm\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Resolve the target user from token or user_id query/header fallback.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('user_id') ?? $request->input('user_id') ?? $request->header('X-User-Id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        return $user;
    }

    /**
     * Resolve the target store for the user.
     */
    protected function resolveStore(Request $request, ?User $user): ?Store
    {
        $storeId = $request->query('store_id') ?? $request->input('store_id') ?? $request->header('X-Store-Id');

        if ($storeId) {
            $storeQuery = Store::where('id', $storeId);
            if ($user) {
                $storeQuery->where('user_id', $user->id);
            }
            return $storeQuery->first();
        }

        if ($user) {
            return Store::where('user_id', $user->id)->first();
        }

        return null;
    }

    /**
     * Transform a WalletTransaction model into the TransactionType schema.
     */
    protected function formatTransaction(WalletTransaction $tx): array
    {
        return $tx->toApiArray();
    }

    /**
     * Preview store balance and latest transactions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $store = $this->resolveStore($request, $user);

        if (! $user && ! $store) {
            return response()->json([
                'total_balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DZD',
                'level' => 'store',
                'store_id' => null,
                'data' => [],
            ], 200);
        }

        $userId = $store ? $store->user_id : $user->id;

        // Retrieve or initialize store-level or user-level wallet
        if ($store) {
            $wallet = Wallet::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'level' => Wallet::LEVEL_STORE,
                ],
                [
                    'user_id' => $userId,
                    'type' => 'store',
                    'balance' => 0.00,
                    'pending_balance' => 0.00,
                    'currency' => 'DZD',
                    'status' => 'verified',
                    'is_identity_verified' => true,
                ]
            );
        } else {
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $userId,
                    'level' => Wallet::LEVEL_USER,
                    'type' => 'balance',
                ],
                [
                    'balance' => 0.00,
                    'pending_balance' => 0.00,
                    'currency' => 'DZD',
                    'status' => 'verified',
                    'is_identity_verified' => true,
                ]
            );
        }

        $limit = max(1, min(20, (int) $request->query('limit', 4)));

        $query = WalletTransaction::where(function ($q) use ($wallet, $store, $userId) {
            $q->where('wallet_id', $wallet->id);
            if ($store) {
                $q->orWhere('store_id', $store->id)
                  ->orWhere(function ($sub) use ($store, $userId) {
                      $sub->where('user_id', $userId)
                          ->where(function ($meta) use ($store) {
                              $meta->where('metadata->store_id', $store->id)
                                   ->orWhere('metadata->store_id', (string) $store->id);
                          });
                  });
            }
        });

        $latestTransactions = $query->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (WalletTransaction $tx) => $this->formatTransaction($tx));

        return response()->json([
            'total_balance' => (float) ($wallet->balance ?? 0.00),
            'pending_balance' => (float) ($wallet->pending_balance ?? 0.00),
            'currency' => (string) ($wallet->currency ?? 'DZD'),
            'store_id' => $store ? (int) $store->id : null,
            'level' => $store ? 'store' : 'user',
            'wallet_id' => (int) $wallet->id,
            'data' => $latestTransactions,
        ], 200);
    }

    /**
     * Paginated list of all store transactions with next_page.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $store = $this->resolveStore($request, $user);

        if (! $user && ! $store) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 20,
                'total' => 0,
                'next_page' => null,
                'total_balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DZD',
                'level' => 'store',
                'store_id' => null,
            ], 200);
        }

        $userId = $store ? $store->user_id : $user->id;

        if ($store) {
            $wallet = Wallet::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'level' => Wallet::LEVEL_STORE,
                ],
                [
                    'user_id' => $userId,
                    'type' => 'store',
                    'balance' => 0.00,
                    'pending_balance' => 0.00,
                    'currency' => 'DZD',
                    'status' => 'verified',
                    'is_identity_verified' => true,
                ]
            );
        } else {
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $userId,
                    'level' => Wallet::LEVEL_USER,
                    'type' => 'balance',
                ],
                [
                    'balance' => 0.00,
                    'pending_balance' => 0.00,
                    'currency' => 'DZD',
                    'status' => 'verified',
                    'is_identity_verified' => true,
                ]
            );
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        $query = WalletTransaction::where(function ($q) use ($wallet, $store, $userId) {
            $q->where('wallet_id', $wallet->id);
            if ($store) {
                $q->orWhere('store_id', $store->id)
                  ->orWhere(function ($sub) use ($store, $userId) {
                      $sub->where('user_id', $userId)
                          ->where(function ($meta) use ($store) {
                              $meta->where('metadata->store_id', $store->id)
                                   ->orWhere('metadata->store_id', (string) $store->id);
                          });
                  });
            }
        });

        $paginator = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);

        $formattedData = collect($paginator->items())->map(
            fn (WalletTransaction $tx) => $this->formatTransaction($tx)
        );

        return response()->json([
            'data' => $formattedData,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $paginator->hasMorePages() ? ($paginator->currentPage() + 1) : null,
            'total_balance' => (float) ($wallet->balance ?? 0.00),
            'pending_balance' => (float) ($wallet->pending_balance ?? 0.00),
            'currency' => (string) ($wallet->currency ?? 'DZD'),
            'store_id' => $store ? (int) $store->id : null,
            'level' => $store ? 'store' : 'user',
            'wallet_id' => (int) $wallet->id,
        ], 200);
    }
}
