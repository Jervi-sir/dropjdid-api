<?php

use App\Http\Controllers\Api\Users\CreatorDropsController;
use App\Http\Controllers\Api\Users\SearchController;
use App\Http\Controllers\Api\Users\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->middleware('optional-sanctum')->group(function () {
    Route::get('/search', SearchController::class);
    Route::get('/{user}', ShowController::class);
    Route::get('/{user}/creator-drops', CreatorDropsController::class);
});
