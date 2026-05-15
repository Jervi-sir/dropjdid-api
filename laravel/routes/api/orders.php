<?php

use App\Http\Controllers\Api\Orders\GetOrderStatusController;
use App\Http\Controllers\Api\Orders\ListOrdersController;
use App\Http\Controllers\Api\Orders\ShowOrderInfoController;
use App\Http\Controllers\Api\Orders\ClaimOrderIssueController;
use App\Http\Controllers\Api\Orders\DeleteOrderController;
use App\Http\Controllers\Api\Orders\GetAvailableDeliveriesController;
use App\Http\Controllers\Api\Orders\CreateOrderController;
use App\Http\Controllers\Api\Orders\PurchaseProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/available-deliveries', GetAvailableDeliveriesController::class);
    Route::get('/products/{productId}', [PurchaseProductController::class, 'getProductInfo']);
    Route::post('/products/{productId}/purchase', CreateOrderController::class);

    Route::get('/', ListOrdersController::class);
    Route::get('/{orderId}', GetOrderStatusController::class);
    Route::get('/{orderId}/info', ShowOrderInfoController::class);
    Route::post('/{orderId}/claim-issue', ClaimOrderIssueController::class);
    Route::delete('/{orderId}', DeleteOrderController::class);
});
