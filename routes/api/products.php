<?php

use App\Http\Controllers\Api\Products\DropsController;
use App\Http\Controllers\Api\Products\GetStorePreviewController;
use App\Http\Controllers\Api\Products\LikeController;
use App\Http\Controllers\Api\Common\UploadController;
use App\Http\Controllers\Api\Products\RefreshController;
use App\Http\Controllers\Api\Products\SaveController;
use App\Http\Controllers\Api\Products\SearchController;
use App\Http\Controllers\Api\Products\ShowController;
use App\Http\Controllers\Api\Products\UpsertProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->middleware('optional-sanctum')->group(function () {
    Route::post('/upload', UploadController::class);
    Route::post('/{product}/refresh', RefreshController::class);
    Route::get('/store-preview/{store}', GetStorePreviewController::class);
    Route::get('/search', SearchController::class);
    Route::get('/{product}', [ShowController::class, 'show']);
    Route::get('/{product}/drops', [DropsController::class, 'index']);
    Route::get('/{product}/suggestions', [ShowController::class, 'suggest']);
    Route::post('/{product}/like', LikeController::class);
    Route::post('/{product}/save', SaveController::class);
    Route::post('/', UpsertProductController::class);
    Route::post('/{product}/upsert', UpsertProductController::class);
});
