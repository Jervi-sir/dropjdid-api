<?php

use App\Http\Controllers\Api\Feeds\DropsFeedController;
use App\Http\Controllers\Api\Feeds\ProductsFeedController;
use Illuminate\Support\Facades\Route;

Route::prefix('feeds')->group(function () {
    Route::get('drops', DropsFeedController::class);
    Route::get('products', [ProductsFeedController::class, 'index']);
});
