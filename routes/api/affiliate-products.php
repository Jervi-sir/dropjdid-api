<?php

use App\Http\Controllers\Api\AffiliateProduct\ListController;
use Illuminate\Support\Facades\Route;

Route::prefix('affiliate-products')->group(function () {
    Route::get('/', [ListController::class, 'index'])->name('api.affiliate-products.index');
    Route::get('/labels/{label}/products', [ListController::class, 'labelProducts'])->name('api.affiliate-products.labels.products');
});
