<?php

use App\Http\Controllers\Api\Sgm\BecomeSgm\BecomeSGMController;
use App\Http\Controllers\Api\Sgm\Stores\ListMyStoresController;
use App\Http\Controllers\Api\Sgm\Stores\ListProductsController;
use App\Http\Controllers\Api\Sgm\Stores\ListPendingRejectedProductsController;
use App\Http\Controllers\Api\Sgm\Stores\LoginToStoreController;
use App\Http\Controllers\Api\Sgm\Stores\Orders\ActionOrderController;
use App\Http\Controllers\Api\Sgm\Stores\Orders\ListOrdersByProductsController;
use App\Http\Controllers\Api\Sgm\Stores\Orders\OrderDetailsController;
use App\Http\Controllers\Api\Sgm\Stores\PreviewStoreController;
use App\Http\Controllers\Api\Sgm\Stores\Products\CalculatePrizeController;
use App\Http\Controllers\Api\Sgm\Stores\Products\DeleteProductController;
use App\Http\Controllers\Api\Sgm\Stores\Products\ShowProductController;
use App\Http\Controllers\Api\Sgm\Stores\Products\UpsertProductController;
use App\Http\Controllers\Api\Sgm\Stores\UpsertStoreController;
use App\Http\Controllers\Api\Sgm\Stores\Wallets\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('sgm')->middleware('auth:sanctum')->group(function () {
    Route::prefix('become-sgm')->group(function () {
        Route::get('show', [BecomeSGMController::class, 'show']);
        Route::post('store', [BecomeSGMController::class, 'store']);
    });

    Route::prefix('stores')->group(function () {
        Route::get('/', ListMyStoresController::class);
        Route::post('upsert', UpsertStoreController::class);
        Route::post('login', LoginToStoreController::class);
        Route::get('{store_id}/preview', PreviewStoreController::class);

        // Store Wallets
        Route::prefix('{store_id}/wallet')->group(function () {
            Route::get('/', [WalletController::class, 'show']);
            Route::post('verify-identity', [WalletController::class, 'verifyIdentity']);
            Route::get('transactions', [WalletController::class, 'listTransactions']);
            Route::get('check-identity', [WalletController::class, 'checkIdentity']);
            Route::post('withdrawal-request', [WalletController::class, 'storeWithdrawalRequest']);
        });

        Route::get('{store_id}/products', ListProductsController::class);
        Route::get('{store_id}/products/pending-rejected', ListPendingRejectedProductsController::class);
        Route::get('{store_id}/products/{product_id}', ShowProductController::class);
        Route::delete('{store_id}/products/{product_id}/delete', DeleteProductController::class);
        Route::post('{store_id}/products/upsert/{product_id?}', UpsertProductController::class);
        Route::post('{store_id}/products/calculate-price', CalculatePrizeController::class);
        Route::get('{store_id}/orders-by-products', [ListOrdersByProductsController::class, 'listProducts']);
        Route::get('{store_id}/orders-by-products/{product_id}', [ListOrdersByProductsController::class, 'listProductOrders']);
        Route::get('{store_id}/orders/{order_id}', OrderDetailsController::class);
        Route::post('{store_id}/orders/{order_id}/accept', [ActionOrderController::class, 'accept']);
        Route::post('{store_id}/orders/{order_id}/decline', [ActionOrderController::class, 'decline']);
        Route::post('{store_id}/orders/{order_id}/claim', [ActionOrderController::class, 'claim']);
    });
});
