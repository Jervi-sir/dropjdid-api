<?php

use App\Http\Controllers\Api\AffiliateLibrary\AffiliateLibraryController;
use Illuminate\Support\Facades\Route;

Route::prefix('affiliate-library')->middleware('optional-sanctum')->group(function () {
    Route::get('/', AffiliateLibraryController::class);

});
