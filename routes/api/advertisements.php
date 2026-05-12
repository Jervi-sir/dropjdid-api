<?php

use App\Http\Controllers\Api\Advertisements\ShowAdvertisementController;
use App\Http\Controllers\Api\Advertisements\SuggestAdvertisementsController;
use Illuminate\Support\Facades\Route;

Route::prefix('advertisements')->middleware('optional-sanctum')->group(function () {
    Route::get('suggest', SuggestAdvertisementsController::class);
    Route::get('{advertisement_id}', ShowAdvertisementController::class);
});
