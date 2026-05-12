<?php

use App\Http\Controllers\Api\Products\LikeProductController;
use App\Http\Controllers\Api\Products\SaveProductController;
use App\Http\Controllers\Api\Products\ShowDropsController;
use App\Http\Controllers\Api\Products\ShowProductController;
use App\Http\Controllers\Api\Products\SuggestProductsController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::prefix('{product_id}')->group(function () {
        Route::get('/', ShowProductController::class);
        Route::post('like', LikeProductController::class);
        Route::post('save', SaveProductController::class);
        Route::get('drops', ShowDropsController::class);
        Route::get('suggest', SuggestProductsController::class);
    });
});
