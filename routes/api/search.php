<?php

use App\Http\Controllers\Api\Search\DropsSearchController;
use App\Http\Controllers\Api\Search\HistoryController;
use App\Http\Controllers\Api\Search\PeopleSearchController;
use App\Http\Controllers\Api\Search\ProductsSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->group(function () {
    Route::get('drops', DropsSearchController::class);
    Route::get('people', PeopleSearchController::class);
    Route::get('products', ProductsSearchController::class);
    Route::prefix('history')->group(function () {
        Route::get('list', [HistoryController::class, 'list']);
        Route::get('suggestions', [HistoryController::class, 'suggestions']);
        Route::post('store', [HistoryController::class, 'store']);
        Route::delete('destroy', [HistoryController::class, 'destroy']);
        Route::delete('clear', [HistoryController::class, 'clear']);
    });
});
