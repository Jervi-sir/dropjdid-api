<?php

use App\Http\Controllers\Api\Product\DropsController;
use App\Http\Controllers\Api\Product\ShowController;
use App\Http\Controllers\Api\UserInteraction\LikeController;
use App\Http\Controllers\Api\UserInteraction\RepostController;
use App\Http\Controllers\Api\UserInteraction\SaveController;
use App\Http\Controllers\Api\UserInteraction\ShareController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::get('/{id}', [ShowController::class, 'show'])->name('api.products.show');
    Route::get('/{id}/drops', [DropsController::class, 'index'])->name('api.products.drops');
    Route::post('/{id}/repost', [RepostController::class, 'toggleProduct'])->name('api.products.repost');
});
