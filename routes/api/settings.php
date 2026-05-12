<?php

use App\Http\Controllers\Api\Drops\SaveDropController;
use App\Http\Controllers\Api\Settings\ContactsController;
use App\Http\Controllers\Api\Settings\FollowedCreatorsController;
use App\Http\Controllers\Api\Settings\FriendsController;
use App\Http\Controllers\Api\Settings\LearningUpdatesController;
use App\Http\Controllers\Api\Settings\MyProfileController;
use App\Http\Controllers\Api\Settings\SeekSupportTeamController;
use App\Http\Controllers\Api\Settings\UpdatePasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::get('update-password', UpdatePasswordController::class);
    Route::prefix('contacts')->group(function () {
        Route::get('list', [ContactsController::class, 'getMyContacts']);
        Route::post('upsert', [ContactsController::class, 'upsertContact']);
        Route::delete('delete', [ContactsController::class, 'deleteContact']);
    });

    Route::get('followed-creators', FollowedCreatorsController::class);
    Route::get('friends', FriendsController::class);
    Route::get('learning-updates', LearningUpdatesController::class);
    Route::get('my-profile', MyProfileController::class);

    Route::get('saved-drops', SaveDropController::class);
    Route::get('seek-support', SeekSupportTeamController::class);

});
