<?php

use App\Http\Controllers\Api\Creators\AffiliateLibraryController;
use App\Http\Controllers\Api\Creators\BecomeCreator\BecomeCreatorController;
use App\Http\Controllers\Api\Creators\Drops\ListLikesController;
use App\Http\Controllers\Api\Creators\Drops\ListSavesController;
use App\Http\Controllers\Api\Creators\Drops\MyDropsController;
use App\Http\Controllers\Api\Creators\Drops\UpsertDropController;
use Illuminate\Support\Facades\Route;

Route::prefix('creators')->middleware('auth:sanctum')->group(function () {
    Route::get('become-creator/show', [BecomeCreatorController::class, 'show']);
    Route::post('become-creator/submit', [BecomeCreatorController::class, 'submit']);

    Route::post('drops/upsert/{drop?}', UpsertDropController::class);
    Route::get('my-drops', [MyDropsController::class, 'listDrops']);
    Route::get('drops/{drop_id}/likes', ListLikesController::class);
    Route::get('drops/{drop_id}/saves', ListSavesController::class);
    Route::get('drops/{drop_id}/products', [MyDropsController::class, 'products']);
    Route::get('affiliate-library', [AffiliateLibraryController::class, 'suggest']);
    Route::get('affiliate-library/search', [AffiliateLibraryController::class, 'search']);
});
