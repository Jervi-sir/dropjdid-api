<?php

use App\Http\Controllers\Api\Creator\MyDropsController;
use App\Http\Controllers\Api\Drop\ShowController;
use App\Http\Controllers\Api\Drop\ShowProductsController;
use App\Http\Controllers\Api\UserInteraction\LikeController;
use App\Http\Controllers\Api\UserInteraction\RepostController;
use App\Http\Controllers\Api\UserInteraction\SaveController;
use App\Http\Controllers\Api\UserInteraction\ShareController;
use Illuminate\Support\Facades\Route;

Route::prefix('drops')->group(function () {
    Route::get('/my-drops', [MyDropsController::class, 'index'])->name('api.drops.my-drops');
    Route::get('/{id}', [ShowController::class, 'show'])->name('api.drops.show');
    Route::get('/{id}/products', [ShowProductsController::class, 'show'])->name('api.drops.products');
    Route::post('/{id}/repost', [RepostController::class, 'toggleDrop'])->name('api.drops.repost');
});
