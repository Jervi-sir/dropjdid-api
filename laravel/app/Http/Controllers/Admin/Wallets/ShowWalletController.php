<?php

namespace App\Http\Controllers\Admin\Wallets;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowWalletController extends Controller
{
    /**
     * Display the wallet details, transaction list, and withdrawal requests.
     */
    public function show(Request $request, Wallet $wallet): Response|JsonResponse
    {
        $wallet->load(['user']);

        // Load recent transactions
        $transactions = $wallet->transactions()
            ->latest()
            ->get()
            ->map(fn ($tx) => [
                'id' => $tx->id,
                'direction' => $tx->direction, // string helper via accessor
                'direction_raw' => $tx->getRawOriginal('direction'),
                'type' => $tx->type, // string helper via accessor
                'type_raw' => $tx->getRawOriginal('type'),
                'status' => $tx->status, // string helper via accessor
                'status_raw' => $tx->getRawOriginal('status'),
                'amount' => $tx->amount,
                'balance_before' => $tx->balance_before,
                'balance_after' => $tx->balance_after,
                'title' => $tx->title,
                'reference' => $tx->reference,
                'created_at' => $tx->created_at?->toIso8601String(),
            ]);

        // Load withdrawal requests associated with this wallet
        $withdrawals = WithdrawalRequest::where('user_id', $wallet->user_id)
            ->latest()
            ->get()
            ->map(fn ($wr) => [
                'id' => $wr->id,
                'amount' => $wr->amount,
                'method' => WithdrawalRequest::METHOD[$wr->method] ?? 'unknown',
                'method_raw' => $wr->method,
                'status' => WithdrawalRequest::STATUS[$wr->status] ?? 'unknown',
                'status_raw' => $wr->status,
                'transaction_id' => $wr->transaction_id,
                'payment_details' => $wr->payment_details,
                'admin_note' => $wr->admin_note,
                'identity_checked_at' => $wr->identity_checked_at?->toIso8601String(),
                'approved_at' => $wr->approved_at?->toIso8601String(),
                'paid_at' => $wr->paid_at?->toIso8601String(),
                'created_at' => $wr->created_at?->toIso8601String(),
            ]);

        $formattedWallet = [
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

        if ($request->wantsJson()) {
            return response()->json([
                'wallet' => $formattedWallet,
                'transactions' => $transactions,
                'withdrawals' => $withdrawals,
            ]);
        }

        return Inertia::render('admin/wallets/show', [
            'wallet' => $formattedWallet,
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Update the wallet profile and verification statuses.
     */
    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:'.implode(',', array_keys(Wallet::STATUSES)),
            'is_identity_verified' => 'required|boolean',
        ]);

        $wallet->update($validated);

        return back()->with('success', 'Wallet updated successfully.');
    }

    /**
     * Update a specific withdrawal request's status and logs.
     */
    public function updateWithdrawal(Request $request, Wallet $wallet, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:'.implode(',', array_keys(WithdrawalRequest::STATUS)),
            'admin_note' => 'nullable|string',
        ]);

        $oldStatus = (int) $withdrawalRequest->status;
        $newStatus = (int) $validated['status'];

        $updateData = [
            'status' => $newStatus,
            'admin_note' => $validated['admin_note'] ?? $withdrawalRequest->admin_note,
        ];

        // Set state action timestamps
        if ($newStatus === WithdrawalRequest::STATUS_APPROVED) {
            $updateData['approved_at'] = now();
        } elseif ($newStatus === WithdrawalRequest::STATUS_PAID) {
            $updateData['paid_at'] = now();
        } elseif ($newStatus === WithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK) {
            $updateData['identity_checked_at'] = now();
        }

        // Apply wallet balance updates (if status transitioning)
        if ($oldStatus !== $newStatus) {
            if (in_array($newStatus, [WithdrawalRequest::STATUS_REJECTED, WithdrawalRequest::STATUS_CANCELLED, WithdrawalRequest::STATUS_FAILED])) {
                if (in_array($oldStatus, [WithdrawalRequest::STATUS_PENDING, WithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK, WithdrawalRequest::STATUS_APPROVED])) {
                    // Refund the amount back to balance
                    $wallet->increment('balance', $withdrawalRequest->amount);
                    $wallet->decrement('pending_balance', $withdrawalRequest->amount);
                }
            } elseif ($newStatus === WithdrawalRequest::STATUS_PAID) {
                if (in_array($oldStatus, [WithdrawalRequest::STATUS_PENDING, WithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK, WithdrawalRequest::STATUS_APPROVED])) {
                    // final payout - deduct from pending balance
                    $wallet->decrement('pending_balance', $withdrawalRequest->amount);
                }
            }
        }

        $withdrawalRequest->update($updateData);

        return back()->with('success', 'Withdrawal request updated successfully.');
    }
}
