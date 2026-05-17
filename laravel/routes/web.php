<?php

use App\Http\Controllers\Admin\Drops\ListDropsController;
use App\Http\Controllers\Admin\Drops\ShowDropController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('admin/drops', ListDropsController::class)->name('admin.drops.index');
    Route::get('admin/drops/{drop}', [ShowDropController::class, 'show'])->name('admin.drops.show');
    Route::put('admin/drops/{drop}', [ShowDropController::class, 'update'])->name('admin.drops.update');
});

require __DIR__.'/settings.php';
