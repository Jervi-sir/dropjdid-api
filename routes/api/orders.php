<?php

use App\Http\Controllers\Api\Orders\ListController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListController::class);
});
