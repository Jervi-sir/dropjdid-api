<?php

use App\Http\Controllers\Api\Catalog\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/catalogs', CatalogController::class);
