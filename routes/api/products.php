<?php

use App\Http\Controllers\Api\Products\SaveController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->middleware('optional-sanctum')->group(function () {
    Route::post('/{product}/save', SaveController::class)->middleware('auth:sanctum');
});
