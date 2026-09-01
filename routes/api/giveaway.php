<?php

use App\Http\Controllers\Api\Giveway\GivewayDetailController;
use App\Http\Controllers\Api\Giveway\GivewayJoinController;
use App\Http\Controllers\Api\Giveway\GivewayPreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('giveaway')->group(function () {
    Route::get('/', [GivewayDetailController::class, 'show'])->name('api.giveaway.active');
    Route::get('/preview', GivewayPreviewController::class)->name('api.giveaway.preview');
    Route::get('/preview/{id}', GivewayPreviewController::class)->name('api.giveaway.preview.show');
    Route::get('/join', [GivewayJoinController::class, 'status'])->name('api.giveaway.join.status');
    Route::post('/join', [GivewayJoinController::class, 'join'])->name('api.giveaway.join');
    Route::get('/{id}', [GivewayDetailController::class, 'show'])->name('api.giveaway.show');
});

