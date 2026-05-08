<?php

use App\Http\Controllers\Api\Products\DropsController;
use App\Http\Controllers\Api\Products\LikeController;
use App\Http\Controllers\Api\Products\SaveController;
use App\Http\Controllers\Api\Products\SearchController;
use App\Http\Controllers\Api\Products\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->middleware('optional-sanctum')->group(function () {
    Route::get('/search', SearchController::class);
    Route::get('/{product}', [ShowController::class, 'show']);
    Route::get('/{product}/drops', [DropsController::class, 'index']);
    Route::get('/{product}/suggestions', [ShowController::class, 'suggest']);
    Route::post('/{product}/like', LikeController::class)->middleware('auth:sanctum');
    Route::post('/{product}/save', SaveController::class)->middleware('auth:sanctum');
});
