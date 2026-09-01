<?php

use App\Http\Controllers\Api\Friends\FriendRequestController;
use App\Http\Controllers\Api\Friends\ShareToFriendsController;
use Illuminate\Support\Facades\Route;

Route::prefix('friends')->group(function () {
    // Friends Listing & Sharing
    Route::get('/', [ShareToFriendsController::class, 'index'])->name('api.friends.index');
    Route::get('/share', [ShareToFriendsController::class, 'index'])->name('api.friends.share');

    // Friend Requests
    Route::get('/requests', [FriendRequestController::class, 'index'])->name('api.friends.requests.index');
    Route::get('/requests/{id}', [FriendRequestController::class, 'show'])->name('api.friends.requests.show');
    Route::post('/requests', [FriendRequestController::class, 'send'])->name('api.friends.requests.send');
    Route::post('/requests/{id}/accept', [FriendRequestController::class, 'accept'])->name('api.friends.requests.accept');
    Route::post('/requests/{id}/reject', [FriendRequestController::class, 'reject'])->name('api.friends.requests.reject');
    Route::delete('/requests/{id}', [FriendRequestController::class, 'cancel'])->name('api.friends.requests.cancel');

    // Unfriend / Remove Friendship
    Route::delete('/{id}', [FriendRequestController::class, 'unfriend'])->name('api.friends.unfriend');
    Route::post('/unfriend', [FriendRequestController::class, 'unfriend'])->name('api.friends.unfriend.post');
});
