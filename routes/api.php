<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__.'/api/auth.php';
require __DIR__.'/api/catalog.php';
require __DIR__.'/api/advertisements.php';
require __DIR__.'/api/conversations.php';
require __DIR__.'/api/drops.php';
require __DIR__.'/api/friends.php';
require __DIR__.'/api/learning.php';
require __DIR__.'/api/notifications.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/prizes.php';
require __DIR__.'/api/products.php';
require __DIR__.'/api/search.php';
require __DIR__.'/api/users.php';
