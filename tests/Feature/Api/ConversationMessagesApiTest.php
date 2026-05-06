<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createConversationFixture(): array
{
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0669100001',
        'password' => Hash::make('password123'),
    ]);

    $friend = User::query()->create([
        'role_id' => $role->id,
        'username' => 'friend',
        'phone_number' => '0669100002',
        'password' => Hash::make('password123'),
    ]);

    $conversation = Conversation::query()->create([
        'type' => 'private',
        'first_user_id' => $viewer->id,
        'second_user_id' => $friend->id,
    ]);

    foreach (range(1, 3) as $index) {
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $index % 2 === 0 ? $viewer->id : $friend->id,
            'type' => 'text',
            'body' => 'Message '.$index,
            'created_at' => now()->subMinutes(4 - $index),
            'updated_at' => now()->subMinutes(4 - $index),
        ]);
    }

    return compact('viewer', 'friend', 'conversation');
}

test('authenticated users can fetch conversation messages with pagination', function () {
    $fixture = createConversationFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/conversations/'.$fixture['conversation']->id.'?per_page=2')
        ->assertOk()
        ->assertJsonPath('conversation.id', $fixture['conversation']->id)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.message', 'Message 3')
        ->assertJsonPath('next_page', 2);
});

test('authenticated users can send and delete their own messages', function () {
    $fixture = createConversationFixture();

    $sendResponse = $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/conversations/'.$fixture['conversation']->id.'/messages', [
            'type' => 'text',
            'body' => 'My latest message',
        ]);

    $messageId = $sendResponse->json('data.id');

    $sendResponse
        ->assertCreated()
        ->assertJsonPath('data.message', 'My latest message')
        ->assertJsonPath('data.isMine', true);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->deleteJson('/api/conversations/'.$fixture['conversation']->id.'/messages/'.$messageId)
        ->assertOk();

    expect(Message::query()->whereKey($messageId)->exists())->toBeFalse();
});

test('authenticated users can soft delete a conversation', function () {
    $fixture = createConversationFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->deleteJson('/api/conversations/'.$fixture['conversation']->id)
        ->assertOk();

    expect(Conversation::withTrashed()->find($fixture['conversation']->id)?->deleted_at)->not->toBeNull();
});
