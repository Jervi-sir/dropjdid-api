<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UsernameAvailabilityController;
use App\Http\Controllers\Api\Settings\AccountRestoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class);
    Route::post('register', RegisterController::class);
    Route::get('username-availability', UsernameAvailabilityController::class);
    Route::post('restore-account', AccountRestoreController::class)->middleware(['resolve-soft-deleted']);

    Route::middleware(['resolve-soft-deleted'])->group(function () {
        Route::post('logout', LogoutController::class);
        Route::get('me', MeController::class);
    });
});
