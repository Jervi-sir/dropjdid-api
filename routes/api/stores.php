<?php

use App\Http\Controllers\Api\Stores\ListProductsController;
use App\Http\Controllers\Api\Stores\SGMController;
use App\Http\Controllers\Api\Stores\ShowController;
use App\Http\Controllers\Api\Stores\UpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [SGMController::class, 'upsert']);
    Route::get('/my-stores', [SGMController::class, 'list']);
    Route::get('/my-stores/{id}/preview', [SGMController::class, 'preview']);
    Route::get('/{store}/products', ListProductsController::class);
    Route::get('/{store}', ShowController::class);
    Route::patch('/{store}', UpdateController::class);
});
