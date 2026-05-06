<?php

use App\Http\Controllers\Api\Learning\ListVideosController;
use Illuminate\Support\Facades\Route;

Route::prefix('learning')->middleware('optional-sanctum')->group(function () {
    Route::get('/videos', ListVideosController::class);
});
