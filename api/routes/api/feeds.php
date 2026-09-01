<?php

use App\Http\Controllers\Api\Feeds\DropsFeedController;
use App\Http\Controllers\Api\Feeds\ExploreController;
use App\Http\Controllers\Api\Feeds\PeopleController;
use App\Http\Controllers\Api\Feeds\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('feeds')->group(function () {
    Route::get('/drops', [DropsFeedController::class, 'index'])->name('api.feeds.drops');
    Route::get('/explore', [ExploreController::class, 'index'])->name('api.feeds.explore');
    Route::get('/explore/labels/{label}/products', [ExploreController::class, 'labelProducts'])->name('api.feeds.explore.label.products');
    Route::get('/people', [PeopleController::class, 'index'])->name('api.feeds.people');
    Route::get('/search/suggestions', [SearchController::class, 'suggestKeywords'])->name('api.feeds.search.suggestions');
});

Route::get('/people', [PeopleController::class, 'index'])->name('api.people');
Route::get('/search/suggestions', [SearchController::class, 'suggestKeywords'])->name('api.search.suggestions');
Route::get('/labels/{label}/products', [ExploreController::class, 'labelProducts'])->name('api.labels.products');
