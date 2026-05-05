<?php

use App\Models\Conversation;
use App\Models\Drop;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createFriendsShareFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0660000001',
        'password' => Hash::make('password123'),
    ]);

    $friendOne = User::query()->create([
        'role_id' => $role->id,
        'username' => 'alpha',
        'phone_number' => '0660000002',
        'password' => Hash::make('password123'),
    ]);

    $friendTwo = User::query()->create([
        'role_id' => $role->id,
        'username' => 'bravo',
        'phone_number' => '0660000003',
        'password' => Hash::make('password123'),
    ]);

    $notFriend = User::query()->create([
        'role_id' => $role->id,
        'username' => 'charlie',
        'phone_number' => '0660000004',
        'password' => Hash::make('password123'),
    ]);

    Friendship::query()->create([
        'sender_id' => $user->id,
        'receiver_id' => $friendOne->id,
        'status' => 'accepted',
        'accepted_at' => now()->subMinute(),
    ]);

    Friendship::query()->create([
        'sender_id' => $friendTwo->id,
        'receiver_id' => $user->id,
        'status' => 'accepted',
        'accepted_at' => now(),
    ]);

    Friendship::query()->create([
        'sender_id' => $user->id,
        'receiver_id' => $notFriend->id,
        'status' => 'pending',
    ]);

    $drop = Drop::query()->create([
        'creator_id' => $user->id,
        'title' => 'Shared drop',
        'status' => 'published',
    ]);

    return [
        'user' => $user,
        'friendOne' => $friendOne,
        'friendTwo' => $friendTwo,
        'notFriend' => $notFriend,
        'drop' => $drop,
    ];
}

test('authenticated users can list accepted friends with pagination and search', function () {
    $fixture = createFriendsShareFixture();

    $this->actingAs($fixture['user'], 'sanctum')
        ->getJson('/api/friends/share?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $fixture['friendTwo']->id)
        ->assertJsonPath('next_page', 2);

    $this->actingAs($fixture['user'], 'sanctum')
        ->getJson('/api/friends/share?search=alp')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $fixture['friendOne']->id)
        ->assertJsonPath('next_page', null);
});

test('authenticated users can share a drop with an accepted friend', function () {
    $fixture = createFriendsShareFixture();

    $response = $this->actingAs($fixture['user'], 'sanctum')
        ->postJson('/api/friends/share', [
            'friend_id' => $fixture['friendOne']->id,
            'item_type' => 'drop',
            'item_id' => $fixture['drop']->id,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Item shared successfully.');

    expect(Conversation::query()->count())->toBe(1)
        ->and(Message::query()->count())->toBe(1)
        ->and(Message::query()->first()?->type)->toBe('text')
        ->and(Message::query()->first()?->attachable_type)->toBe(Drop::class)
        ->and(Message::query()->first()?->attachable_id)->toBe($fixture['drop']->id);
});
