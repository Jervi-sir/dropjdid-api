<?php

namespace App\Http\Controllers\Api\Wallets;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RefundWalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $request->user()->refundWallet()->firstOrCreate([
            'user_id' => $request->user()->id,
            'type' => Wallet::TYPE_REFUND,
        ], [
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'DZD',
            'status' => Wallet::STATUS_NEW,
            'is_identity_verified' => false,
        ]);

        return response()->json([
            'wallet' => $wallet,
        ]);
    }

    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // This is a placeholder for actual password verification logic.
        // Usually, you'd check if the password matches the user's password.
        // Assuming there's a password_plaintext for demo or just checking against hashed.
        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 422);
        }

        $wallet = $user->refundWallet;
        if (! $wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        $wallet->update([
            'status' => Wallet::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Identity verification request submitted',
            'wallet' => $wallet,
        ]);
    }

    public function listTransactions(Request $request)
    {
        $wallet = $request->user()->refundWallet;

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

    public function checkIdentity(Request $request)
    {
        return response()->json([
            'identity_checked' => (bool) $request->user()->identity_verified_at,
        ]);
    }

    public function storeWithdrawalRequest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:'.implode(',', [
                'baridimob',
                'ccp',
                'bank_transfer',
                'cash',
            ]),
            'payment_details' => 'required|array',
        ]);

        $user = $request->user();
        $wallet = $user->refundWallet;

        if (! $wallet || $wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        return DB::transaction(function () use ($request, $wallet, $user) {
            $balanceBefore = $wallet->balance;
            $wallet->decrement('balance', $request->amount);
            $balanceAfter = $wallet->balance;

            $transaction = $wallet->transactions()->create([
                'user_id' => $user->id,
                'direction' => WalletTransaction::DIRECTION_OUT,
                'type' => WalletTransaction::TYPE_REQUEST_WITHDRAWAL,
                'status' => WalletTransaction::STATUS_PENDING,
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'title' => 'Withdrawal Request ('.ucfirst($request->method).')',
                'metadata' => [
                    'method' => $request->method,
                    'payment_details' => $request->payment_details,
                ],
            ]);

            $withdrawalRequest = $transaction->withdrawalRequest()->create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => $request->method,
                'payment_details' => $request->payment_details,
                'status' => 'pending', // Default status for WithdrawalRequest
            ]);

            return response()->json([
                'message' => 'Withdrawal request submitted successfully',
                'transaction' => $transaction,
                'withdrawal_request' => $withdrawalRequest,
            ]);
        });
    }
}
