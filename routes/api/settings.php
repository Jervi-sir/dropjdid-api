<?php

use App\Http\Controllers\Api\Settings\AboutUsController;
use App\Http\Controllers\Api\Settings\SupportController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::get('/about-us', AboutUsController::class)->name('api.settings.about-us');

    Route::post('/support', [SupportController::class, 'store'])->name('api.settings.support.store');
    Route::get('/support/status', [SupportController::class, 'status'])->name('api.settings.support.status');
    Route::get('/support', [SupportController::class, 'index'])->name('api.settings.support.index');
});

// Direct aliases
Route::get('/about-us', AboutUsController::class)->name('api.about-us');
Route::post('/support-request', [SupportController::class, 'store'])->name('api.support.store');
Route::get('/support-request/status', [SupportController::class, 'status'])->name('api.support.status');

