<?php

use App\Http\Controllers\Api\Catalogs\DeliveryCatalogController;
use App\Http\Controllers\Api\Catalogs\FilterCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalogs')->group(function () {
    Route::get('/filters', FilterCatalogController::class)->name('api.catalogs.filters');

    // Delivery catalog routes
    Route::prefix('delivery')->group(function () {
        // 1. List Wilayas
        Route::get('/wilayas', [DeliveryCatalogController::class, 'getWilayas'])->name('api.catalogs.delivery.wilayas');

        // 2. List Delivery Available in this Wilaya and prices (home and stopdesk)
        Route::get('/options/{wilayaId?}', [DeliveryCatalogController::class, 'getDeliveryOptions'])->name('api.catalogs.delivery.options');

        // 3. List Communes (Baladiyas) under a Wilaya & Stopdesk
        Route::get('/communes/{wilayaId?}', [DeliveryCatalogController::class, 'getCommunes'])->name('api.catalogs.delivery.communes');

        // 4. List Delivery Companies
        Route::get('/companies', [DeliveryCatalogController::class, 'getCompanies'])->name('api.catalogs.delivery.companies');
    });
});
