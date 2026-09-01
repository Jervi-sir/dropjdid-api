<?php

use App\Http\Controllers\Admin\Orders\ListOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [ListOrderController::class, 'index'])->name('index');
    Route::post('/{order}/status', [ListOrderController::class, 'updateStatus'])->name('status.update');
});
