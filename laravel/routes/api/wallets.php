<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Wallets\WalletController;
use App\Http\Controllers\Api\Wallets\RefundWalletController;

Route::prefix('wallets')->middleware('auth:sanctum')->group(function () {
    // Main Balance Wallet
    Route::get('/', [WalletController::class, 'show']);
    Route::get('/transactions', [WalletController::class, 'listTransactions']);
    Route::post('/withdrawal-request', [WalletController::class, 'storeWithdrawalRequest']);
    Route::get('/check-identity', [WalletController::class, 'checkIdentity']);
    Route::post('/verify-identity', [WalletController::class, 'verifyIdentity']);

    // Refund Wallet
    Route::prefix('refund')->group(function () {
        Route::get('/', [RefundWalletController::class, 'show']);
        Route::get('/transactions', [RefundWalletController::class, 'listTransactions']);
        Route::post('/withdrawal-request', [RefundWalletController::class, 'storeWithdrawalRequest']);
        Route::get('/check-identity', [RefundWalletController::class, 'checkIdentity']);
        Route::post('/verify-identity', [RefundWalletController::class, 'verifyIdentity']);
    });
});
