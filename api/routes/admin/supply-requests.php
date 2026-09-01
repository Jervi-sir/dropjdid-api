<?php

use App\Http\Controllers\Admin\SupplyRequests\SupplyRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/supply-requests')->name('admin.supply-requests.')->group(function () {
    Route::get('/', [SupplyRequestController::class, 'index'])->name('index');
    Route::post('/', [SupplyRequestController::class, 'store'])->name('store');
    Route::post('/{supplyRequest}/status', [SupplyRequestController::class, 'updateStatus'])->name('status.update');
    Route::post('/{supplyRequest}/receive', [SupplyRequestController::class, 'receiveItems'])->name('receive');
});
