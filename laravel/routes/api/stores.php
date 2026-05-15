<?php

use App\Http\Controllers\Api\Stores\ListProductsController;
use App\Http\Controllers\Api\Stores\ShowStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ShowStoreController::class);
    Route::get('{store_id}', ListProductsController::class);
});
