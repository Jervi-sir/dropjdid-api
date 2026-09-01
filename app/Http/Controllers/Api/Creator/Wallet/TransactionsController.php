<?php

namespace App\Http\Controllers\Api\Creator\Wallet;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Resolve the target creator user from token or query/header fallback.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('creator_id')
                ?? $request->query('user_id')
                ?? $request->input('user_id')
                ?? $request->header('X-User-Id');

            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            // Fallback for dev / mock mode to find a creator or default user
            $user = User::whereHas('roles', fn ($q) => $q->where('code', 'creator'))
                ->orWhereHas('roles', fn ($q) => $q->where('name', 'creator'))
                ->first()
                ?? User::first();
        }

        return $user;
    }

    /**
     * Transform a WalletTransaction model into the TransactionType schema expected by the frontend.
     */
    protected function formatTransaction(WalletTransaction $tx): array
    {
        return $tx->toApiArray();
    }

    /**
     * Preview creator balance and latest transactions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return response()->json([
                'total_balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DA',
                'level' => Wallet::LEVEL_CREATOR,
                'data' => [],
            ], 200);
        }

        // Retrieve or initialize creator wallet
        $wallet = Wallet::firstOrCreate(
            [
                'user_id' => $user->id,
                'level' => Wallet::LEVEL_CREATOR,
                'type' => 'balance',
            ],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DA',
                'status' => 'verified',
                'is_identity_verified' => true,
            ]
        );

        $limit = max(1, min(20, (int) $request->query('limit', 4)));

        $latestTransactions = WalletTransaction::where(function ($query) use ($wallet, $user) {
            $query->where('wallet_id', $wallet->id)
                ->orWhere('user_id', $user->id);
        })
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (WalletTransaction $tx) => $this->formatTransaction($tx));

        return response()->json([
            'total_balance' => (float) ($wallet->balance ?? 0.00),
            'pending_balance' => (float) ($wallet->pending_balance ?? 0.00),
            'currency' => (string) ($wallet->currency ?? 'DA'),
            'level' => Wallet::LEVEL_CREATOR,
            'user_id' => (int) $user->id,
            'data' => $latestTransactions,
        ], 200);
    }

    /**
     * Paginated list of all creator transactions with next_page.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 20,
                'total' => 0,
                'next_page' => null,
                'total_balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DA',
                'level' => Wallet::LEVEL_CREATOR,
            ], 200);
        }

        $wallet = Wallet::firstOrCreate(
            [
                'user_id' => $user->id,
                'level' => Wallet::LEVEL_CREATOR,
                'type' => 'balance',
            ],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'DA',
                'status' => 'verified',
                'is_identity_verified' => true,
            ]
        );

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        $query = WalletTransaction::where(function ($q) use ($wallet, $user) {
            $q->where('wallet_id', $wallet->id)
                ->orWhere('user_id', $user->id);
        });

        // Optional type filter (e.g. 'drops', 'request-withdrawal', 'bonus', 'refund')
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        // Optional status filter (e.g. 'completed', 'pending')
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

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
            'currency' => (string) ($wallet->currency ?? 'DA'),
            'level' => Wallet::LEVEL_CREATOR,
            'user_id' => (int) $user->id,
        ], 200);
    }
}
