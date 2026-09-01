<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    $isParticipant = Conversation::query()
        ->where('id', (int) $id)
        ->where(function ($q) use ($user) {
            $q->where('first_user_id', $user->id)
              ->orWhere('second_user_id', $user->id);
        })
        ->exists();

    if (! $isParticipant) {
        return false;
    }

    return [
        'id' => (int) $user->id,
        'name' => $user->name ?? $user->username ?? 'User',
        'image_url' => $user->image_url ?? $user->avatar_url ?? null,
    ];
});
