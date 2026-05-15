<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createConversationsListFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0559100001',
        'password' => Hash::make('password123'),
    ]);

    $friend = User::query()->create([
        'role_id' => $role->id,
        'username' => 'friend',
        'phone_number' => '0559100002',
        'password' => Hash::make('password123'),
    ]);

    $other = User::query()->create([
        'role_id' => $role->id,
        'username' => 'other',
        'phone_number' => '0559100003',
        'password' => Hash::make('password123'),
    ]);

    $secondFriend = User::query()->create([
        'role_id' => $role->id,
        'username' => 'secondfriend',
        'phone_number' => '0559100004',
        'password' => Hash::make('password123'),
    ]);

    foreach (range(1, 2) as $index) {
        $conversation = Conversation::query()->create([
            'type' => 'private',
            'first_user_id' => $viewer->id,
            'second_user_id' => $index === 1 ? $friend->id : $secondFriend->id,
            'first_user_last_read_at' => $index === 1 ? now()->subMinute() : now()->subHour(),
        ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $index === 1 ? $friend->id : $secondFriend->id,
            'type' => 'text',
            'body' => 'Message '.$index,
            'created_at' => now()->subMinutes(3 - $index),
            'updated_at' => now()->subMinutes(3 - $index),
        ]);
    }

    $hiddenConversation = Conversation::query()->create([
        'type' => 'private',
        'first_user_id' => $friend->id,
        'second_user_id' => $other->id,
    ]);

    return [
        'viewer' => $viewer,
    ];
}

test('authenticated users can list their conversations with pagination', function () {
    $fixture = createConversationsListFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/conversations?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('next_page', 2)
        ->assertJsonPath('data.0.user.name', 'friend');
});

test('guests cannot list conversations', function () {
    createConversationsListFixture();

    $this->getJson('/api/conversations')->assertUnauthorized();
});
