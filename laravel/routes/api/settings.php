<?php

use App\Http\Controllers\Api\Creators\ListMyFollowersController;
use App\Http\Controllers\Api\Settings\ContactsController;
use App\Http\Controllers\Api\Settings\FollowedCreatorsController;
use App\Http\Controllers\Api\Settings\FriendsController;
use App\Http\Controllers\Api\Settings\LearningUpdatesController;
use App\Http\Controllers\Api\Settings\ListSavedItemsController;
use App\Http\Controllers\Api\Settings\MyProfileController;
use App\Http\Controllers\Api\Settings\SeekSupportTeamController;
use App\Http\Controllers\Api\Settings\UpdatePasswordController;
use App\Http\Controllers\Api\Settings\AccounDeletionController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->middleware('auth:sanctum')->group(function () {
    Route::get('update-password', UpdatePasswordController::class);
    Route::delete('delete-account', AccounDeletionController::class);
    Route::prefix('contacts')->group(function () {
        Route::get('list', [ContactsController::class, 'getMyContacts']);
        Route::post('upsert', [ContactsController::class, 'upsertContact']);
        Route::delete('delete/{contact_id}', [ContactsController::class, 'deleteContact']);
    });

    Route::get('followed-creators', FollowedCreatorsController::class);
    Route::get('friends', FriendsController::class);
    Route::get('learning-updates', LearningUpdatesController::class);
    Route::get('my-profile', [MyProfileController::class, 'show']);
    Route::put('my-profile', [MyProfileController::class, 'update']);

    Route::get('saved-drops', [ListSavedItemsController::class, 'listDrops']);
    Route::get('saved-products', [ListSavedItemsController::class, 'listProducts']);
    Route::get('seek-support', [SeekSupportTeamController::class, 'listHistory']);
    Route::post('seek-support', [SeekSupportTeamController::class, 'store']);
    Route::get('my-followers', ListMyFollowersController::class);

});
