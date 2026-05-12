<?php

use App\Http\Controllers\Api\Drops\LikeDropController;
use App\Http\Controllers\Api\Drops\SaveDropController;
use App\Http\Controllers\Api\Products\ShowDropsController;
use Illuminate\Support\Facades\Route;

Route::prefix('drops')->middleware('auth:sanctum')->group(function () {
    Route::get('{drop_id}', ShowDropsController::class);
    Route::post('{drop_id}/like', LikeDropController::class);
    Route::post('{drop_id}/save', SaveDropController::class);

});
