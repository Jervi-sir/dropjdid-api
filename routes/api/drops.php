<?php

use App\Http\Controllers\Api\Drops\LikeController;
use App\Http\Controllers\Api\Drops\ListController;
use App\Http\Controllers\Api\Drops\SaveController;
use App\Http\Controllers\Api\Drops\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('drops')->middleware('optional-sanctum')->group(function () {
    Route::get('/', ListController::class);
    Route::get('/search', SearchController::class);
    Route::post('/{drop}/like', LikeController::class)->middleware('auth:sanctum');
    Route::post('/{drop}/save', SaveController::class)->middleware('auth:sanctum');

});
