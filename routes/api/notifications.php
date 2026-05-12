<?php

use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Http\Controllers\NotificationsController;

Route::prefix('notifications')->group(function () {
    Route::get('/', NotificationsController::class);
});
