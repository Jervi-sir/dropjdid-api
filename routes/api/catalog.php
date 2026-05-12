<?php

use App\Http\Controllers\Api\Catalog\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalog')->group(function () {
    Route::get('/', CatalogController::class);
});
