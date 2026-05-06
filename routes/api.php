<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__.'/api/auth.php';
require __DIR__.'/api/advertisements.php';
require __DIR__.'/api/drops.php';
require __DIR__.'/api/friends.php';
require __DIR__.'/api/products.php';
