<?php

use App\Http\Controllers\Api\MyAccount\EditMyAccountController;
use App\Http\Controllers\Api\MyAccount\ListFollowedCreatorsController;
use App\Http\Controllers\Api\MyAccount\ListMyFriendsController;
use App\Http\Controllers\Api\MyAccount\ListSavedItemsController;
use App\Http\Controllers\Api\MyAccount\ListSavedProductsController;
use App\Http\Controllers\Api\MyAccount\MyAccountController;
use App\Http\Controllers\Api\MyAccount\PasswordController;
use App\Http\Controllers\Api\MyAccount\UserContactController;
use Illuminate\Support\Facades\Route;

Route::prefix('my-account')->group(function () {
    Route::get('/', [MyAccountController::class, 'show'])->name('api.my-account.show');
    Route::match(['post', 'put'], '/', [MyAccountController::class, 'update'])->name('api.my-account.update');
    Route::get('/edit-profile', [EditMyAccountController::class, 'show'])->name('api.my-account.edit-profile.show');
    Route::post('/edit-profile', [EditMyAccountController::class, 'update'])->name('api.my-account.edit-profile.update');
    Route::post('/change-password', [PasswordController::class, 'update'])->name('api.my-account.change-password');
    Route::match(['post', 'put'], '/password', [PasswordController::class, 'update'])->name('api.my-account.password');
    Route::get('/friends', ListMyFriendsController::class)->name('api.my-account.friends');
    Route::get('/followed-creators', ListFollowedCreatorsController::class)->name('api.my-account.followed-creators');
    Route::get('/saved-products', ListSavedProductsController::class)->name('api.my-account.saved-products');
    Route::get('/saved-items', ListSavedItemsController::class)->name('api.my-account.saved-items');

    // User Contacts
    Route::get('/contacts', [UserContactController::class, 'index'])->name('api.my-account.contacts.index');
    Route::get('/contacts/platforms', [UserContactController::class, 'platforms'])->name('api.my-account.contacts.platforms');
    Route::post('/contacts', [UserContactController::class, 'store'])->name('api.my-account.contacts.store');
    Route::put('/contacts/{id}', [UserContactController::class, 'update'])->name('api.my-account.contacts.update');
    Route::delete('/contacts/{id}', [UserContactController::class, 'destroy'])->name('api.my-account.contacts.destroy');
});



