<?php

use App\Http\Controllers\Api\Prizes\ParticipateController;
use App\Http\Controllers\Api\Prizes\ShowCurrentController;
use Illuminate\Support\Facades\Route;

Route::prefix('prizes')->middleware('optional-sanctum')->group(function () {
    Route::get('/current/preview', [ShowCurrentController::class, 'preview']);
    Route::get('/current', [ShowCurrentController::class, 'showFully']);
    Route::post('/current/participate', ParticipateController::class)->middleware('auth:sanctum');
});
