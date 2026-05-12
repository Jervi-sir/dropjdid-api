<?php

use App\Http\Controllers\Api\Prizes\ParticipatePrizeController;
use App\Http\Controllers\Api\Prizes\ShowCurrentPrizeController;
use Illuminate\Support\Facades\Route;

Route::prefix('prizes')->group(function () {
    Route::get('/', [ShowCurrentPrizeController::class, 'preview']);
    Route::post('{prize_id}/join', ParticipatePrizeController::class);
    Route::get('{prize_id}/details', [ShowCurrentPrizeController::class, 'showFully']);
});
