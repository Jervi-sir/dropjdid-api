<?php

use App\Http\Controllers\Api\Catalog\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalog')->middleware('optional-sanctum')->group(function () {
    Route::get('/', CatalogController::class);
});
