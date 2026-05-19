<?php

use App\Http\Controllers\Api\Search\DropsSearchController;
use App\Http\Controllers\Api\Search\HistorySearchController;
use App\Http\Controllers\Api\Search\PeopleSearchController;
use App\Http\Controllers\Api\Search\ProductsSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->middleware('auth:sanctum')->group(function () {
    Route::get('drops', DropsSearchController::class);
    Route::get('people', PeopleSearchController::class);
    Route::get('products', ProductsSearchController::class);

    Route::prefix('history')->group(function () {
        Route::get('/', [HistorySearchController::class, 'list']);
        Route::post('/', [HistorySearchController::class, 'store']);
        Route::get('suggestions', [HistorySearchController::class, 'suggestions']);
        Route::delete('{history}', [HistorySearchController::class, 'destroy']);
        Route::delete('/', [HistorySearchController::class, 'clear']);
    });
});
