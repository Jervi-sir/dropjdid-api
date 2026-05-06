<?php

use App\Http\Controllers\Api\Conversations\ListController;
use Illuminate\Support\Facades\Route;

Route::prefix('conversations')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListController::class);
});
