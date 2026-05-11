<?php

use App\Http\Controllers\Api\Settings\BeacomeCreatorController;
use App\Http\Controllers\Api\Settings\ChangePasswordController;
use App\Http\Controllers\Api\Settings\BecomeSGMController;
use App\Http\Controllers\Api\Settings\ContactsController;
use App\Http\Controllers\Api\Settings\FollowedCreatorsController;
use App\Http\Controllers\Api\Settings\FriendsController;
use App\Http\Controllers\Api\Settings\LearningUpdatesController;
use App\Http\Controllers\Api\Settings\MyProfileController;
use App\Http\Controllers\Api\Settings\SavedDropsProductsController;
use App\Http\Controllers\Api\Settings\SeekSupportTeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->middleware('optional-sanctum')->group(function () {
    Route::get('my-profile', [MyProfileController::class, 'show']);
    Route::put('my-profile', [MyProfileController::class, 'update']);
    Route::get('friends', FriendsController::class);
    Route::get('followed-creators', FollowedCreatorsController::class);
    Route::get('saved/products', [SavedDropsProductsController::class, 'products']);
    Route::get('saved/drops', [SavedDropsProductsController::class, 'drops']);
    Route::get('contacts', [ContactsController::class, 'getMyContacts']);
    Route::post('contacts', [ContactsController::class, 'upsertContact']);
    Route::delete('contacts/{contact}', [ContactsController::class, 'deleteContact']);
    Route::get('become-creator', [BeacomeCreatorController::class, 'show']);
    Route::post('become-creator', [BeacomeCreatorController::class, 'store']);

    Route::get('learning-updates', LearningUpdatesController::class);
    Route::post('change-password', ChangePasswordController::class)->middleware('auth:sanctum');
    Route::get('become-sgm', [BecomeSGMController::class, 'show'])->middleware('auth:sanctum');
    Route::post('become-sgm', [BecomeSGMController::class, 'store'])->middleware('auth:sanctum');
    Route::get('contact-support', [SeekSupportTeamController::class, 'index'])->middleware('auth:sanctum');
    Route::post('contact-support', [SeekSupportTeamController::class, 'store'])->middleware('auth:sanctum');
});
