<?php

use App\Http\Controllers\Api\Profiles\ListDropsController;
use App\Http\Controllers\Api\Profiles\SendFollowCreatorController;
use App\Http\Controllers\Api\Profiles\ShowProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('profiles')->middleware('auth:sanctum')->group(function () {
    Route::prefix('{user}')->group(function () {
        Route::get('/', ShowProfileController::class);
        Route::post('follow', SendFollowCreatorController::class);
        Route::get('drops', ListDropsController::class);
    });
});
