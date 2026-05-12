<?php

use App\Http\Controllers\Api\Feeds\DropsFeedController;
use App\Http\Controllers\Api\Feeds\LabelFeedController;
use App\Http\Controllers\Api\Feeds\ProductsFeedController;
use Illuminate\Support\Facades\Route;

Route::prefix('feeds')->middleware('auth:sanctum')->group(function () {
    Route::get('drops', [DropsFeedController::class, 'list']);
    Route::get('drops/{drop}/products', [DropsFeedController::class, 'products']);
    Route::get('products', [ProductsFeedController::class, 'index']);
    Route::get('labels', [LabelFeedController::class, 'index']);
    Route::get('labels/{label}/products', [LabelFeedController::class, 'labelProducts']);
});
