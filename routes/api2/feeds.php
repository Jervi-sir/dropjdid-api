<?php

use App\Http\Controllers\Api\Feeds\DropsController;
use App\Http\Controllers\Api\Feeds\ProductsController;
use Illuminate\Support\Facades\Route;

Route::prefix('feeds')->middleware('optional-sanctum')->group(function () {
    Route::get('drops', DropsController::class);
    Route::get('products', [ProductsController::class, 'index']);
    Route::get('products/labels/{label}', [ProductsController::class, 'labelProducts']);
});
