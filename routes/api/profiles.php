<?php

use App\Http\Controllers\Api\Profiles\CreatorDropsController;
use App\Http\Controllers\Api\Profiles\SendFollowCreatorController;
use App\Http\Controllers\Api\Profiles\ShowProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')->group(function () {
    Route::prefix('{profile_id}')->group(function () {
        Route::get('/', ShowProfileController::class);
        Route::post('follow', SendFollowCreatorController::class);
        Route::get('drops', CreatorDropsController::class);
    });
});
