<?php

use App\Http\Controllers\Api\Prizes\ParticipatePrizeController;
use App\Http\Controllers\Api\Prizes\ShowCurrentPrizeController;
use Illuminate\Support\Facades\Route;

Route::prefix('prizes')->middleware('auth:sanctum')->group(function () {
    Route::get('current', [ShowCurrentPrizeController::class, 'current']);
    Route::post('{prize_id}/join', ParticipatePrizeController::class);
    Route::get('{prize_id}/details', [ShowCurrentPrizeController::class, 'showFully']);
});
