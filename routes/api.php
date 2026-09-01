<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting Authentication for API & Mobile Clients
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__.'/api/auth.php';
require __DIR__.'/api/feeds.php';
require __DIR__.'/api/ads.php';
require __DIR__.'/api/events.php';
require __DIR__.'/api/giveaway.php';
require __DIR__.'/api/conversations.php';
require __DIR__.'/api/drops.php';
require __DIR__.'/api/products.php';
require __DIR__.'/api/friends.php';
require __DIR__.'/api/notifications.php';
require __DIR__.'/api/catalogs.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/my-account.php';
require __DIR__.'/api/creators.php';
require __DIR__.'/api/sgm.php';
require __DIR__.'/api/people.php';
require __DIR__.'/api/affiliate-products.php';
require __DIR__.'/api/interactions.php';
require __DIR__.'/api/settings.php';






