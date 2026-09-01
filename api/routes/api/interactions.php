<?php

use App\Http\Controllers\Api\UserInteraction\LikeController;
use App\Http\Controllers\Api\UserInteraction\RepostController;
use App\Http\Controllers\Api\UserInteraction\SaveController;
use App\Http\Controllers\Api\UserInteraction\ShareController;
use Illuminate\Support\Facades\Route;

Route::prefix('interactions')->group(function () {
    // Ads Interactions
    Route::prefix('ads')->group(function () {
        Route::post('/{id}/like', [LikeController::class, 'toggleAd'])->name('api.interactions.ads.like');
        Route::post('/{id}/save', [SaveController::class, 'toggleAd'])->name('api.interactions.ads.save');
        Route::post('/{id}/share', [ShareController::class, 'shareAd'])->name('api.interactions.ads.share');
        Route::post('/{id}/repost', [RepostController::class, 'toggleAd'])->name('api.interactions.ads.repost');
    });

    // Drops Interactions
    Route::prefix('drops')->group(function () {
        Route::post('/{id}/like', [LikeController::class, 'toggleDrop'])->name('api.interactions.drops.like');
        Route::post('/{id}/save', [SaveController::class, 'toggleDrop'])->name('api.interactions.drops.save');
        Route::post('/{id}/share', [ShareController::class, 'shareDrop'])->name('api.interactions.drops.share');
        Route::post('/{id}/repost', [RepostController::class, 'toggleDrop'])->name('api.interactions.drops.repost');
    });

    // Products Interactions
    Route::prefix('products')->group(function () {
        Route::post('/{id}/like', [LikeController::class, 'toggleProduct'])->name('api.interactions.products.like');
        Route::post('/{id}/save', [SaveController::class, 'toggleProduct'])->name('api.interactions.products.save');
        Route::post('/{id}/share', [ShareController::class, 'shareProduct'])->name('api.interactions.products.share');
        Route::post('/{id}/repost', [RepostController::class, 'toggleProduct'])->name('api.interactions.products.repost');
    });

    // Profile / People Interactions
    Route::prefix('profiles')->group(function () {
        Route::post('/{id}/share', [ShareController::class, 'shareProfile'])->name('api.interactions.profiles.share');
    });
    Route::prefix('people')->group(function () {
        Route::post('/{id}/share', [ShareController::class, 'shareProfile'])->name('api.interactions.people.share');
    });

    // Repost Listings
    Route::get('/my-reposts', [RepostController::class, 'myReposts'])->name('api.interactions.my-reposts');
    Route::get('/users/{userId}/reposts', [RepostController::class, 'userReposts'])->name('api.interactions.user-reposts');
});
