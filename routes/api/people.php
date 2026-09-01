<?php

use App\Http\Controllers\Api\People\CreatorDropsController;
use App\Http\Controllers\Api\People\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('people')->group(function () {
    Route::get('/{id}', [ShowController::class, 'show'])->name('api.people.show');
    Route::get('/{id}/contacts', [ShowController::class, 'contacts'])->name('api.people.contacts');
    Route::get('/{id}/drops', [CreatorDropsController::class, 'index'])->name('api.people.drops');
});
