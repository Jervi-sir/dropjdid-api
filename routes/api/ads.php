<?php

use App\Http\Controllers\Api\Ads\AdController;
use App\Http\Controllers\Api\Ads\ShowController;
use App\Http\Controllers\Api\UserInteraction\LikeController;
use App\Http\Controllers\Api\UserInteraction\SaveController;
use App\Http\Controllers\Api\UserInteraction\ShareController;
use Illuminate\Support\Facades\Route;

Route::prefix('ads')->group(function () {
    Route::get('/', [AdController::class, 'index'])->name('api.ads.index');
    Route::get('/{id}', ShowController::class)->name('api.ads.show');
});
