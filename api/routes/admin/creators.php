<?php

use App\Http\Controllers\Admin\Creators\ListDropController;
use App\Http\Controllers\Admin\Creators\ListRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/creators')->name('admin.creators.')->group(function () {
    Route::get('/requests', [ListRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests/{creatorRequest}/approve', [ListRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{creatorRequest}/reject', [ListRequestController::class, 'reject'])->name('requests.reject');

    // Drops Management
    Route::get('/drops', [ListDropController::class, 'index'])->name('drops.index');
    Route::put('/drops/{drop}', [ListDropController::class, 'update'])->name('drops.update');
    Route::post('/drops/{drop}/status', [ListDropController::class, 'updateStatus'])->name('drops.status');
    Route::delete('/drops/{drop}', [ListDropController::class, 'destroy'])->name('drops.destroy');
});
