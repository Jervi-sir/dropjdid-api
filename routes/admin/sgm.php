<?php

use App\Http\Controllers\Admin\Sgm\ListProductController;
use App\Http\Controllers\Admin\Sgm\ListRequestController;
use App\Http\Controllers\Admin\Sgm\ListStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/sgm')->name('admin.sgm.')->group(function () {
    Route::get('/requests', [ListRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests/{sgmRequest}/approve', [ListRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{sgmRequest}/reject', [ListRequestController::class, 'reject'])->name('requests.reject');

    Route::get('/stores', [ListStoreController::class, 'index'])->name('stores.index');
    Route::post('/stores/{store}/approve', [ListStoreController::class, 'approve'])->name('stores.approve');
    Route::post('/stores/{store}/status', [ListStoreController::class, 'updateStatus'])->name('stores.status');

    Route::get('/products', [ListProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/approve', [ListProductController::class, 'approve'])->name('products.approve');
    Route::post('/products/{product}/reject', [ListProductController::class, 'reject'])->name('products.reject');
    Route::post('/products/{product}/archive', [ListProductController::class, 'archive'])->name('products.archive');
    Route::post('/products/{product}/status', [ListProductController::class, 'updateStatus'])->name('products.status');
});

