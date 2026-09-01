<?php

use App\Http\Controllers\Api\Events\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('api.events.index');
    Route::post('/', [EventController::class, 'store'])->name('api.events.store');
    Route::get('/{id}', [EventController::class, 'show'])->name('api.events.show');
});
