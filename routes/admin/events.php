<?php

use App\Http\Controllers\Admin\Events\EventListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/events')->name('admin.events.')->group(function () {
    Route::get('/', [EventListController::class, 'index'])->name('index');
    Route::post('/', [EventListController::class, 'store'])->name('store');
    Route::put('/{event}', [EventListController::class, 'update'])->name('update');
    Route::delete('/{event}', [EventListController::class, 'destroy'])->name('destroy');
    Route::post('/{event}/toggle-status', [EventListController::class, 'toggleStatus'])->name('toggle-status');
});
