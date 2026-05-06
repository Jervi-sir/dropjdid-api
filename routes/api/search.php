<?php

use App\Http\Controllers\Api\Search\HistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->middleware('optional-sanctum')->group(function () {
    Route::get('/history', [HistoryController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/suggestions', [HistoryController::class, 'suggestions']);
});
