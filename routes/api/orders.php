<?php

use App\Http\Controllers\Api\Orders\GetOrderStatusController;
use App\Http\Controllers\Api\Orders\ListOrdersController;
use App\Http\Controllers\Api\Orders\ShowOrderInfoController;
use App\Http\Controllers\Api\Orders\ClaimOrderIssueController;
use App\Http\Controllers\Api\Orders\DeleteOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListOrdersController::class);
    Route::get('/{orderId}', GetOrderStatusController::class);
    Route::get('/{orderId}/info', ShowOrderInfoController::class);
    Route::post('/{orderId}/claim-issue', ClaimOrderIssueController::class);
    Route::delete('/{orderId}', DeleteOrderController::class);
});
