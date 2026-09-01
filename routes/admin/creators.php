<?php

use App\Http\Controllers\Admin\Creators\ListRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/creators')->name('admin.creators.')->group(function () {
    Route::get('/requests', [ListRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests/{creatorRequest}/approve', [ListRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{creatorRequest}/reject', [ListRequestController::class, 'reject'])->name('requests.reject');
});
