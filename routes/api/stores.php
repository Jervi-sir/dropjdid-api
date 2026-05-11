<?php

use App\Http\Controllers\Api\Stores\ListMyStoresController;
use App\Http\Controllers\Api\Stores\ListProductsController;
use App\Http\Controllers\Api\Stores\ShowController;
use App\Http\Controllers\Api\Stores\UpdateController;
use App\Http\Controllers\Api\Stores\UpsertStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::post('/', UpsertStoreController::class);
    Route::get('/my-stores', ListMyStoresController::class);
    Route::get('/{store}/products', ListProductsController::class);
    Route::get('/{store}', ShowController::class);
    Route::patch('/{store}', UpdateController::class);
});
