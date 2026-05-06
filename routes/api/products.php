<?php

use App\Http\Controllers\Api\Products\LikeController;
use App\Http\Controllers\Api\Products\SaveController;
use App\Http\Controllers\Api\Products\ShowController;
use App\Http\Controllers\Api\Products\SuggestController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->middleware('optional-sanctum')->group(function () {
    Route::get('/{product}', ShowController::class);
    Route::get('/{product}/suggestions', SuggestController::class);
    Route::post('/{product}/like', LikeController::class)->middleware('auth:sanctum');
    Route::post('/{product}/save', SaveController::class)->middleware('auth:sanctum');
});
