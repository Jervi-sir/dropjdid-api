<?php

namespace App\Http\Controllers\Admin\StoreWallets;

use App\Http\Controllers\Controller;
use App\Models\StoreWallet;
use App\Models\StoreWithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowStoreWalletController extends Controller
{
    /**
     * Display the store wallet details, transaction list, and withdrawal requests.
     */
    public function show(Request $request, StoreWallet $store_wallet): Response|JsonResponse
    {
        $store_wallet->load(['store']);

        // Load recent transactions
        $transactions = $store_wallet->transactions()
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

        // Load withdrawal requests associated with this store wallet
        $withdrawals = StoreWithdrawalRequest::where('store_id', $store_wallet->store_id)
            ->latest()
            ->get()
            ->map(fn ($wr) => [
                'id' => $wr->id,
                'amount' => $wr->amount,
                'method' => StoreWithdrawalRequest::METHOD[$wr->method] ?? 'unknown',
                'method_raw' => $wr->method,
                'status' => StoreWithdrawalRequest::STATUS[$wr->status] ?? 'unknown',
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
            'id' => $store_wallet->id,
            'store' => $store_wallet->store ? [
                'id' => $store_wallet->store->id,
                'store_name' => $store_wallet->store->store_name,
                'phone_number' => $store_wallet->store->phone_number,
                'logo' => $store_wallet->store->logo,
            ] : null,
            'type' => StoreWallet::TYPES[$store_wallet->type] ?? 'unknown',
            'type_raw' => $store_wallet->type,
            'balance' => $store_wallet->balance,
            'pending_balance' => $store_wallet->pending_balance,
            'is_identity_verified' => $store_wallet->is_identity_verified,
            'status' => StoreWallet::STATUSES[$store_wallet->status] ?? 'unknown',
            'status_raw' => $store_wallet->status,
            'currency' => $store_wallet->currency ?? 'DZD',
            'created_at' => $store_wallet->created_at?->toIso8601String(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'store_wallet' => $formattedWallet,
                'transactions' => $transactions,
                'withdrawals' => $withdrawals,
            ]);
        }

        return Inertia::render('admin/store-wallets/show', [
            'store_wallet' => $formattedWallet,
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Update the store wallet operational and verification status.
     */
    public function update(Request $request, StoreWallet $store_wallet): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:'.implode(',', array_keys(StoreWallet::STATUSES)),
            'is_identity_verified' => 'required|boolean',
        ]);

        $store_wallet->update($validated);

        return back()->with('success', 'Store wallet updated successfully.');
    }

    /**
     * Update status and ledger transitions for a StoreWithdrawalRequest.
     */
    public function updateWithdrawal(Request $request, StoreWallet $store_wallet, StoreWithdrawalRequest $storeWithdrawalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:'.implode(',', array_keys(StoreWithdrawalRequest::STATUS)),
            'admin_note' => 'nullable|string',
        ]);

        $oldStatus = (int) $storeWithdrawalRequest->status;
        $newStatus = (int) $validated['status'];

        $updateData = [
            'status' => $newStatus,
            'admin_note' => $validated['admin_note'] ?? $storeWithdrawalRequest->admin_note,
        ];

        // Set action timestamps based on target state
        if ($newStatus === StoreWithdrawalRequest::STATUS_APPROVED) {
            $updateData['approved_at'] = now();
        } elseif ($newStatus === StoreWithdrawalRequest::STATUS_PAID) {
            $updateData['paid_at'] = now();
        } elseif ($newStatus === StoreWithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK) {
            $updateData['identity_checked_at'] = now();
        }

        // Apply ledger balance updates (if status is transitioning)
        if ($oldStatus !== $newStatus) {
            if (in_array($newStatus, [StoreWithdrawalRequest::STATUS_REJECTED, StoreWithdrawalRequest::STATUS_CANCELLED, StoreWithdrawalRequest::STATUS_FAILED])) {
                if (in_array($oldStatus, [StoreWithdrawalRequest::STATUS_PENDING, StoreWithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK, StoreWithdrawalRequest::STATUS_APPROVED])) {
                    // Refund the withdrawal request amount to store wallet balance
                    $store_wallet->increment('balance', $storeWithdrawalRequest->amount);
                    $store_wallet->decrement('pending_balance', $storeWithdrawalRequest->amount);
                }
            } elseif ($newStatus === StoreWithdrawalRequest::STATUS_PAID) {
                if (in_array($oldStatus, [StoreWithdrawalRequest::STATUS_PENDING, StoreWithdrawalRequest::STATUS_PENDING_IDENTITY_CHECK, StoreWithdrawalRequest::STATUS_APPROVED])) {
                    // final payout - deduct from pending balance
                    $store_wallet->decrement('pending_balance', $storeWithdrawalRequest->amount);
                }
            }
        }

        $storeWithdrawalRequest->update($updateData);

        return back()->with('success', 'Store withdrawal request updated successfully.');
    }
}
