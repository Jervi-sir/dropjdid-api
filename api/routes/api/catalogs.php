<?php

use App\Http\Controllers\Api\Catalogs\FilterCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalogs')->group(function () {
    Route::get('/filters', FilterCatalogController::class)->name('api.catalogs.filters');
});
