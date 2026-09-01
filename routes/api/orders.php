<?php

use App\Http\Controllers\Api\Order\BuySessionController;
use App\Http\Controllers\Api\Order\CancelController;
use App\Http\Controllers\Api\Order\CouponController;
use App\Http\Controllers\Api\Order\ListController;
use App\Http\Controllers\Api\Order\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', ListController::class)->name('api.orders.index');
    Route::get('/buy-session', [BuySessionController::class, 'show'])->name('api.orders.buy-session');
    Route::get('/buy-session/{id}', [BuySessionController::class, 'show'])->name('api.orders.buy-session.id');
    Route::post('/create-order', [BuySessionController::class, 'createOrder'])->name('api.orders.create');
    Route::match(['get', 'post'], '/check-coupon', [CouponController::class, 'check'])->name('api.orders.check-coupon');
    Route::get('/{id}', ShowController::class)->name('api.orders.show');
    Route::post('/{id}/cancel', CancelController::class)->name('api.orders.cancel');
});


