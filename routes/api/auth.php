<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UsernameAvailabilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Public routes
    Route::match(['get', 'post'], '/check-username', [UsernameAvailabilityController::class, 'check'])->name('api.auth.check-username');
    Route::post('/register', [RegisterController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [LoginController::class, 'login'])->name('api.auth.login');
    Route::post('/forgot-password', [\App\Http\Controllers\Api\Settings\SupportController::class, 'store'])->name('api.auth.forgot-password');

    // Protected routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [MeController::class, 'me'])->name('api.auth.me');
        Route::post('/logout', [LogoutController::class, 'logout'])->name('api.auth.logout');
    });
});
