<?php

use App\Http\Controllers\Api\Friends\ListFriendsController;
use App\Http\Controllers\Api\Friends\RequestFriendController;
use App\Http\Controllers\Api\Friends\ShareToFriendController;
use Illuminate\Support\Facades\Route;

Route::prefix('friends')->group(function () {
    Route::get('/', ListFriendsController::class);
    Route::get('to-share-to', ShareToFriendController::class);
    Route::post('{user}/send', [RequestFriendController::class, 'send']);
    Route::post('{user}/accept', [RequestFriendController::class, 'accept']);
    Route::post('{user}/reject', [RequestFriendController::class, 'reject']);
    Route::post('{user}/unfriend', [RequestFriendController::class, 'unfriend']);
});
