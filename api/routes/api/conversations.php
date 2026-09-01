<?php

use App\Http\Controllers\Api\Conversations\ActionsController;
use App\Http\Controllers\Api\Conversations\ListController;
use App\Http\Controllers\Api\Conversations\SettingController;
use App\Http\Controllers\Api\Notifications\TestController;
use Illuminate\Support\Facades\Route;

// Unauthenticated test endpoint for conversation messages
Route::match(['get', 'post'], 'conversations/test', TestController::class)->name('api.conversations.test');

Route::middleware('auth:sanctum')->prefix('conversations')->group(function () {
    Route::get('/', [ListController::class, 'index'])->name('api.conversations.index');
    Route::post('/', [ActionsController::class, 'startConversation'])->name('api.conversations.start');
    Route::post('/share', [ActionsController::class, 'shareToUser'])->name('api.conversations.share');
    Route::get('/{id}', [ActionsController::class, 'show'])->name('api.conversations.show');
    Route::post('/{id}/messages', [ActionsController::class, 'sendMessage'])->name('api.conversations.send-message');
    Route::delete('/{id}/messages/{messageId}', [ActionsController::class, 'deleteMessage'])->name('api.conversations.delete-message');
    Route::post('/{id}/seen', [ActionsController::class, 'markSeen'])->name('api.conversations.mark-seen');
    Route::post('/{id}/clear', [SettingController::class, 'clear'])->name('api.conversations.clear');
    Route::delete('/{id}', [SettingController::class, 'destroy'])->name('api.conversations.delete');
});
