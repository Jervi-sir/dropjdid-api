<?php

use App\Http\Controllers\Api\Conversations\DeleteConversationController;
use App\Http\Controllers\Api\Conversations\DeleteMessageController;
use App\Http\Controllers\Api\Conversations\ListConversationsController;
use App\Http\Controllers\Api\Conversations\SendMessageController;
use App\Http\Controllers\Api\Conversations\ShowConversationController;
use Illuminate\Support\Facades\Route;

Route::prefix('conversations')->group(function () {
    Route::get('/', ListConversationsController::class);
    Route::get('{conversation_id}/messages', ShowConversationController::class);
    Route::delete('{conversation_id}', DeleteConversationController::class);

    Route::post('{conversation_id}/messages', SendMessageController::class);
    Route::delete('{conversation_id}/messages/{message_id}', DeleteMessageController::class);
});
