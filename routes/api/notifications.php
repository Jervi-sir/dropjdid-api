<?php

use App\Http\Controllers\Api\Notifications\ListController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListController::class);
});
