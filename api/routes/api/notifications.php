<?php

use App\Http\Controllers\Api\Notifications\ListController;
use App\Http\Controllers\Api\Notifications\NotificationController;
use App\Http\Controllers\Api\Notifications\TestController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function () {
    Route::get('/', ListController::class)->name('api.notifications.index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::post('/mark-read', [NotificationController::class, 'markAllRead'])->name('api.notifications.mark-read');
    Route::match(['get', 'post'], '/test', TestController::class)->name('api.notifications.test');
    Route::match(['get', 'post'], '/test-send', TestController::class)->name('api.notifications.test-send');
});
