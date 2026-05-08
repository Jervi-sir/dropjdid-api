<?php

use App\Http\Controllers\Api\Search\DropsController;
use App\Http\Controllers\Api\Search\HistoryController;
use App\Http\Controllers\Api\Search\PeopleController;
use App\Http\Controllers\Api\Search\ProductsController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->middleware('optional-sanctum')->group(function () {
    Route::get('/drops', DropsController::class);
    Route::get('/people', PeopleController::class);
    Route::get('/products', ProductsController::class);
    Route::post('/history', [HistoryController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/history', [HistoryController::class, 'index'])->middleware('auth:sanctum');
    Route::delete('/history', [HistoryController::class, 'clear'])->middleware('auth:sanctum');
    Route::delete('/history/{history}', [HistoryController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/suggestions', [HistoryController::class, 'suggestions']);
});
