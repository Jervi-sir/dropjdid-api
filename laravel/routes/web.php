<?php

use App\Models\Drop;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('test', function () {
    $drop = Drop::find(96);
    $drop->addRejectionReason('You must at least add 3 principal pictures', 'You must at least add 3 principal pictures', 'You must at least add 3 principal pictures');
});
