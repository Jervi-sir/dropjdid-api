<?php

use App\Http\Controllers\Api\Advertisements\SuggestController;
use Illuminate\Support\Facades\Route;

Route::prefix('advertisements')->middleware('optional-sanctum')->group(function () {
    Route::get('/suggestions', SuggestController::class);
});
