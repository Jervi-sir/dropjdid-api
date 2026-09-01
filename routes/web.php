<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin/creators.php';
require __DIR__.'/admin/sgm.php';
require __DIR__.'/admin/orders.php';
require __DIR__.'/admin/supply-requests.php';
require __DIR__.'/admin/events.php';

