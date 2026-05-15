<?php

use App\Http\Controllers\Api\Notifications\ListNotificationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListNotificationsController::class);
});
