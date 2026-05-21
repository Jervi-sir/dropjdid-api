<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * --------------------------------------------------------------------------
 * Section Title
 * --------------------------------------------------------------------------
 */
require __DIR__.'/api/advertisements.php'; // []
require __DIR__.'/api/auth.php'; // []
require __DIR__.'/api/catalog.php'; // []
require __DIR__.'/api/conversations.php'; // []
require __DIR__.'/api/creators.php'; // []
require __DIR__.'/api/drops.php'; // []
require __DIR__.'/api/feeds.php'; // []
require __DIR__.'/api/labels.php'; // []
require __DIR__.'/api/friends.php'; // []
require __DIR__.'/api/media.php'; // []
require __DIR__.'/api/notifications.php'; // []
require __DIR__.'/api/orders.php'; // []
require __DIR__.'/api/prizes.php'; // []
require __DIR__.'/api/products.php'; // []
require __DIR__.'/api/profiles.php'; // []
require __DIR__.'/api/search.php'; // []
require __DIR__.'/api/settings.php'; // []
require __DIR__.'/api/sgm.php'; // []
require __DIR__.'/api/stores.php'; // []
require __DIR__.'/api/wallets.php'; // []
