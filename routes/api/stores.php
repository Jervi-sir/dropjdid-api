<?php

use App\Http\Controllers\Api\Stores\CreateNewStoreController;
use App\Http\Controllers\Api\Stores\ListMyStoresController;
use App\Http\Controllers\Api\Stores\ShowController;
use App\Http\Controllers\Api\Stores\UpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::post('/', CreateNewStoreController::class);
    Route::get('/my-stores', ListMyStoresController::class);
    Route::get('/{store}', ShowController::class);
    Route::patch('/{store}', UpdateController::class);
});
