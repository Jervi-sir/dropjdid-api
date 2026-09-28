<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Admin Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    // Authenticated Admin Routes
    Route::middleware('admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
