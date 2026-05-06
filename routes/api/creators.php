<?php

use App\Http\Controllers\Api\Creators\BeacomeController;
use App\Http\Controllers\Api\Creators\ListFollowingController;
use App\Http\Controllers\Api\Creators\ListMyFollowersController;
use App\Http\Controllers\Api\Creators\SendFollowController;
use Illuminate\Support\Facades\Route;

Route::prefix('creators')->middleware('auth:sanctum')->group(function () {
    Route::get('/become', BeacomeController::class);
    Route::post('/become', BeacomeController::class);
    Route::post('/follow/{user}', SendFollowController::class);
    Route::get('/my-followers', ListMyFollowersController::class);
    Route::get('/my-following', ListFollowingController::class);
});
