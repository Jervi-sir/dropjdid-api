<?php

use App\Http\Controllers\Api\CREATOR\AffiliateLibraryController;
use App\Http\Controllers\Api\Creators\BecomeCreator\BecomeCreatorController;
use App\Http\Controllers\Api\Creators\Drops\MyDropsController;
use App\Http\Controllers\Api\Creators\ListMyFollowersController;
use Illuminate\Support\Facades\Route;

Route::prefix('creators')->group(function () {
    Route::prefix('become-creator')->group(function () {
        Route::get('show', [BecomeCreatorController::class, 'show']);
        Route::get('submit', [BecomeCreatorController::class, 'submit']);
    });
    Route::get('my-drops', MyDropsController::class);
    Route::get('affiliate-library', AffiliateLibraryController::class);

    Route::get('list-my-followers', ListMyFollowersController::class);

});
