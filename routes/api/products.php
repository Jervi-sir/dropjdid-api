<?php

use App\Http\Controllers\Api\Products\LikeProductController;
use App\Http\Controllers\Api\Products\PaginateByController;
use App\Http\Controllers\Api\Products\SaveProductController;
use App\Http\Controllers\Api\Products\ShowDropsController;
use App\Http\Controllers\Api\Products\ShowProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::prefix('{product_id}')->group(function () {
        Route::middleware('optional-sanctum')->group(function () {
            Route::get('/', ShowProductController::class);
            Route::get('drops', ShowDropsController::class);
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('like', LikeProductController::class);
            Route::post('save', SaveProductController::class);
        });
    });
});

Route::prefix('paginate-by')->middleware('optional-sanctum')->group(function () {
    Route::get('suggest-by-label/{product_id}', [PaginateByController::class, 'byLabels']);
    Route::get('product/{product_id}', [PaginateByController::class, 'byProductId']);
    Route::get('label/{label_id}', [PaginateByController::class, 'byLabelId']);
    Route::get('drop/{drop_id}', [PaginateByController::class, 'byDropId']);
});
