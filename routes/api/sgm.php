<?php

use App\Http\Controllers\Api\Sgm\AuthStoreController;
use App\Http\Controllers\Api\Sgm\BecomeSgmController;
use App\Http\Controllers\Api\Sgm\ListMyStoresController;
use App\Http\Controllers\Api\Sgm\ThisStore\ThisStoreProductController;
use App\Http\Controllers\Api\Sgm\ThisStore\UpsertProductController;
use App\Http\Controllers\Api\Sgm\TutorialsController;
use App\Http\Controllers\Api\Sgm\Wallet\CheckIdentityController;
use App\Http\Controllers\Api\Sgm\Wallet\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('sgm')->group(function () {
    Route::get('/tutorials', [TutorialsController::class, 'index'])->name('api.sgm.tutorials');
    Route::post('/wallet/check-identity', CheckIdentityController::class)->name('api.sgm.wallet.check-identity');
    Route::get('/wallet/preview', [TransactionsController::class, 'preview'])->name('api.sgm.wallet.preview');
    Route::get('/wallet/transactions', [TransactionsController::class, 'index'])->name('api.sgm.wallet.transactions');

    Route::get('/become-sgm', [BecomeSgmController::class, 'show'])->name('api.sgm.become.status');
    Route::post('/become-sgm', [BecomeSgmController::class, 'submit'])->name('api.sgm.become');
    Route::post('/create-store', [AuthStoreController::class, 'createStore'])->name('api.sgm.store.create');
    Route::post('/login-store', [AuthStoreController::class, 'loginStore'])->name('api.sgm.store.login');
    Route::post('/logout-store', [AuthStoreController::class, 'logoutStore'])->name('api.sgm.store.logout');
    Route::get('/my-stores', ListMyStoresController::class)->name('api.sgm.my-stores');
    Route::get('/this-store/products', [ThisStoreProductController::class, 'index'])->name('api.sgm.this-store.products');
    Route::get('/this-store/types-and-sizes', [UpsertProductController::class, 'typesAndSizes'])->name('api.sgm.this-store.types-sizes');
    Route::get('/this-store/labels', [UpsertProductController::class, 'labels'])->name('api.sgm.this-store.labels');
    Route::get('/this-store/keywords', [UpsertProductController::class, 'keywords'])->name('api.sgm.this-store.keywords');
    Route::get('/this-store/keywords/{labelId}', [UpsertProductController::class, 'keywords'])->name('api.sgm.this-store.keywords.id');
    Route::get('/this-store/upsert-product', [UpsertProductController::class, 'show'])->name('api.sgm.this-store.upsert-product.show');
    Route::get('/this-store/upsert-product/{id}', [UpsertProductController::class, 'show'])->name('api.sgm.this-store.upsert-product.show.id');
    Route::post('/this-store/upsert-product', [UpsertProductController::class, 'store'])->name('api.sgm.this-store.upsert-product.store');
    Route::match(['patch', 'put', 'post'], '/this-store/upsert-product/{id}', [UpsertProductController::class, 'update'])->name('api.sgm.this-store.upsert-product.update');
    Route::post('/this-store/upsert-product/{id}/refresh', [UpsertProductController::class, 'refresh'])->name('api.sgm.this-store.upsert-product.refresh');
    Route::delete('/this-store/upsert-product/{id}', [UpsertProductController::class, 'destroy'])->name('api.sgm.this-store.upsert-product.destroy');
    Route::get('/this-store/settings', [\App\Http\Controllers\Api\Sgm\ThisStore\SettingController::class, 'show'])->name('api.sgm.this-store.settings.show');
    Route::match(['post', 'patch', 'put'], '/this-store/settings', [\App\Http\Controllers\Api\Sgm\ThisStore\SettingController::class, 'update'])->name('api.sgm.this-store.settings.update');
    Route::get('/this-store/settings/{id}', [\App\Http\Controllers\Api\Sgm\ThisStore\SettingController::class, 'show'])->name('api.sgm.this-store.settings.show.id');
    Route::match(['post', 'patch', 'put'], '/this-store/settings/{id}', [\App\Http\Controllers\Api\Sgm\ThisStore\SettingController::class, 'update'])->name('api.sgm.this-store.settings.update.id');
    Route::get('/this-store/supply-requests', [\App\Http\Controllers\Api\Sgm\ThisStore\ThisStoreSupplyRequestController::class, 'index'])->name('api.sgm.this-store.supply-requests');
    Route::post('/this-store/supply-requests/{id}/progress', [\App\Http\Controllers\Api\Sgm\ThisStore\ThisStoreSupplyRequestController::class, 'updateProgress'])->name('api.sgm.this-store.supply-requests.progress');
});
