<?php

use App\Http\Controllers\Api\Advertisements\ShowAdvertisementController;
use App\Http\Controllers\Api\Advertisements\SuggestAdvertisementsController;
use Illuminate\Support\Facades\Route;

Route::prefix('advertisements')->group(function () {
    Route::get('{id}', ShowAdvertisementController::class);
    Route::get('suggest', SuggestAdvertisementsController::class);
});
