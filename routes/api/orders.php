<?php

use App\Http\Controllers\Api\Orders\ListOrdersController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', ListOrdersController::class);
});
