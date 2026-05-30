<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Wallets;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreWallet;
use App\Models\StoreWalletTransaction;
use App\Models\StoreWithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{
    public function show(Request $request, int $store_id)
    {
        $store = Store::where('id', $store_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $wallet = $store->balanceWallet()->firstOrCreate([
            'store_id' => $store->id,
            'type' => StoreWallet::TYPE_BALANCE,
        ], [
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'DZD',
            'status' => StoreWallet::STATUS_NEW,
            'is_identity_verified' => false,
        ]);

        return response()->json([
            'wallet' => $wallet,
        ]);
    }

    public function verifyIdentity(Request $request, $store_id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $store = Store::where('id', $store_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 422);
        }

        $wallet = $store->balanceWallet()->firstOrCreate([
            'store_id' => $store->id,
            'type' => StoreWallet::TYPE_BALANCE,
        ], [
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'DZD',
            'status' => StoreWallet::STATUS_NEW,
            'is_identity_verified' => false,
        ]);

        $wallet->update([
            'status' => StoreWallet::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Identity verification request submitted',
            'wallet' => $wallet,
        ]);
    }

    public function verifyPassword(Request $request, $store_id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $store = Store::where('id', $store_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 422);
        }

        return response()->json(['success' => true]);
    }

    public function listTransactions(Request $request, int $store_id)
    {
        $store = Store::where('id', $store_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $wallet = $store->balanceWallet;

        if (! $wallet) {
            return response()->json([
                'data' => [],
                'total' => 0,
            ]);
        }

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(15);

        return response()->json($transactions);
    }

    public function checkIdentity(Request $request, $store_id)
    {
        $store = Store::where('id', $store_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $wallet = $store->balanceWallet;

        return response()->json([
            'identity_checked' => $wallet ? (bool) $wallet->is_identity_verified : false,
        ]);
    }

    public function storeWithdrawalRequest(Request $request, $store_id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:' . implode(',', [
                'baridimob',
                'ccp',
                'bank_transfer',
                'cash',
            ]),
            'payment_details' => 'required|array',
        ]);

        $user = $request->user();
        $store = Store::where('id', $store_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wallet = $store->balanceWallet;

        if (! $wallet || $wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        return DB::transaction(function () use ($request, $wallet, $store) {
            $balanceBefore = $wallet->balance;
            $wallet->decrement('balance', $request->amount);
            $balanceAfter = $wallet->balance;

            $methodMap = [
                'baridimob' => StoreWithdrawalRequest::METHOD_BARIDIMOB,
                'ccp' => StoreWithdrawalRequest::METHOD_CCP,
                'bank_transfer' => StoreWithdrawalRequest::METHOD_BANK_TRANSFER,
                'cash' => StoreWithdrawalRequest::METHOD_CASH,
            ];
            $methodInt = $methodMap[$request->method] ?? StoreWithdrawalRequest::METHOD_BARIDIMOB;

            $transaction = $wallet->transactions()->create([
                'store_id' => $store->id,
                'direction' => StoreWalletTransaction::DIRECTION_OUT,
                'type' => StoreWalletTransaction::TYPE_REQUEST_WITHDRAWAL,
                'status' => StoreWalletTransaction::STATUS_PENDING,
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'title' => 'Withdrawal Request (' . ucfirst($request->method) . ')',
                'metadata' => [
                    'method' => $request->method,
                    'payment_details' => $request->payment_details,
                ],
            ]);

            $withdrawalRequest = $transaction->storeWithdrawalRequest()->create([
                'store_id' => $store->id,
                'amount' => $request->amount,
                'method' => $methodInt,
                'payment_details' => $request->payment_details,
                'status' => StoreWithdrawalRequest::STATUS_PENDING,
            ]);

            return response()->json([
                'message' => 'Withdrawal request submitted successfully',
                'transaction' => $transaction,
                'withdrawal_request' => $withdrawalRequest,
            ]);
        });
    }
}
