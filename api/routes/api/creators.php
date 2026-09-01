<?php

use App\Http\Controllers\Api\Creator\BecomeCreatorController;
use App\Http\Controllers\Api\Creator\DropStatsController;
use App\Http\Controllers\Api\Creator\FollowersController;
use App\Http\Controllers\Api\Creator\MyDropsController;
use App\Http\Controllers\Api\Creator\SendFollowController;
use App\Http\Controllers\Api\Creator\UpsertDropController;
use App\Http\Controllers\Api\Creator\Wallet\CheckIdentityController;
use App\Http\Controllers\Api\Creator\Wallet\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('creators')->group(function () {
    Route::get('/become-creator', [BecomeCreatorController::class, 'show'])->name('api.creators.become.status');
    Route::post('/become-creator', [BecomeCreatorController::class, 'submit'])->name('api.creators.become');
    Route::post('/follow', SendFollowController::class)->name('api.creators.follow');
    Route::post('/{id}/follow', SendFollowController::class)->name('api.creators.follow.id');
    Route::get('/followers', FollowersController::class)->name('api.creators.followers');
    Route::get('/my-drops', [MyDropsController::class, 'index'])->name('api.creators.my-drops');
    Route::get('/drops/check-title', [UpsertDropController::class, 'checkTitleAvailability'])->name('api.creators.drops.check-title');
    Route::get('/drops/{drop}', [UpsertDropController::class, 'show'])->name('api.creators.drops.show');
    Route::post('/drops', [UpsertDropController::class, 'upsert'])->name('api.creators.drops.upsert');
    Route::delete('/drops/{drop}', [UpsertDropController::class, 'destroy'])->name('api.creators.drops.destroy');
    Route::get('/drops/{drop}/liked-by', [DropStatsController::class, 'likedBy'])->name('api.creators.drops.liked-by');
    Route::get('/drops/{drop}/saved-by', [DropStatsController::class, 'savedBy'])->name('api.creators.drops.saved-by');
    Route::get('/drops/{drop}/shared-by', [DropStatsController::class, 'sharedBy'])->name('api.creators.drops.shared-by');
    Route::get('/drops/{drop}/products', [DropStatsController::class, 'products'])->name('api.creators.drops.products');
    Route::post('/wallet/check-identity', CheckIdentityController::class)->name('api.creators.wallet.check-identity');
    Route::get('/wallet/preview', [TransactionsController::class, 'preview'])->name('api.creators.wallet.preview');
    Route::get('/wallet/transactions', [TransactionsController::class, 'index'])->name('api.creators.wallet.transactions');
});
