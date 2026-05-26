<?php

use App\Http\Controllers\Api\ForgotPassword\ShowController;
use App\Http\Controllers\Api\ForgotPassword\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('forgot-password')->middleware('optional-sanctum')->group(function () {
  Route::get('/', ShowController::class);
  Route::post('/', StoreController::class);
});
