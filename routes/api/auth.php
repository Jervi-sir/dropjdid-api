<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UsernameAvailabilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::get('/username-availability', UsernameAvailabilityController::class);
    Route::post('/login', LoginController::class);
    Route::post('/register', RegisterController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', MeController::class);
        Route::post('/logout', LogoutController::class);
    });
});
