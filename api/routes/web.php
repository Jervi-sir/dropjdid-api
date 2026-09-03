<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/privacy/{target?}', function (\Illuminate\Http\Request $request, ?string $target = null) {
    return view('privacy', [
        'target' => $target ?? $request->query('target', 'dropjdid'),
    ]);
})->name('privacy');

Route::get('/account-deletion/{target?}', function (\Illuminate\Http\Request $request, ?string $target = null) {
    return view('account-deletion', [
        'target' => $target ?? $request->query('target', 'dropjdid'),
    ]);
})->name('account-deletion');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/admin/creators.php';
require __DIR__.'/admin/sgm.php';
require __DIR__.'/admin/orders.php';
require __DIR__.'/admin/supply-requests.php';
require __DIR__.'/admin/events.php';


