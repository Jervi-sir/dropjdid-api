<?php

use App\Http\Controllers\Api\Conversations\DeleteController;
use App\Http\Controllers\Api\Conversations\DeleteMessageController;
use App\Http\Controllers\Api\Conversations\ListController;
use App\Http\Controllers\Api\Conversations\SendMessageController;
use App\Http\Controllers\Api\Conversations\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('conversations')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListController::class);
    Route::get('/{conversation}', ShowController::class);
    Route::post('/{conversation}/messages', SendMessageController::class);
    Route::delete('/{conversation}/messages/{message}', DeleteMessageController::class);
    Route::delete('/{conversation}', DeleteController::class);
});
