<?php

use App\Http\Controllers\Api\Friends\ShareController;
use Illuminate\Support\Facades\Route;

Route::prefix('friends')->middleware('auth:sanctum')->group(function () {
    Route::get('/share', [ShareController::class, 'index']);
    Route::post('/share', [ShareController::class, 'store']);
});
