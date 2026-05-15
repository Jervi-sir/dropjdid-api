<?php

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createNotificationsListFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0555000001',
        'password' => Hash::make('password123'),
    ]);

    $otherUser = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0555000002',
        'password' => Hash::make('password123'),
    ]);

    $types = collect([
        'sales' => 'Sales',
        'withdraw' => 'Withdraw',
        'tracking_order' => 'Tracking Order',
        'friend_request' => 'Friend Request',
        'followers' => 'Followers',
    ])->map(fn (string $name, string $code): NotificationType => NotificationType::query()->create([
        'code' => $code,
        'en' => $name,
    ]));

    Notification::query()->create([
        'notification_type_id' => $types['sales']->id,
        'user_id' => $user->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => [
            'drop_title' => 'Forest Drop',
            'amount' => 200,
            'message' => 'Sale completed',
        ],
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);

    Notification::query()->create([
        'notification_type_id' => $types['withdraw']->id,
        'user_id' => $user->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => [
            'method' => 'Dahabia',
            'amount' => 10000,
            'message' => 'Withdrawal processed',
        ],
        'created_at' => now()->subMinutes(4),
        'updated_at' => now()->subMinutes(4),
    ]);

    Notification::query()->create([
        'notification_type_id' => $types['tracking_order']->id,
        'user_id' => $user->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => [
            'order_number' => '473694',
            'status' => 'Refunded successfully',
        ],
        'created_at' => now()->subMinutes(3),
        'updated_at' => now()->subMinutes(3),
    ]);

    Notification::query()->create([
        'notification_type_id' => $types['friend_request']->id,
        'user_id' => $user->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => [
            'sender_id' => $otherUser->id,
            'sender_name' => 'creator',
            'friendship_id' => 9,
            'status' => 'pending',
        ],
        'created_at' => now()->subMinutes(2),
        'updated_at' => now()->subMinutes(2),
    ]);

    Notification::query()->create([
        'notification_type_id' => $types['followers']->id,
        'user_id' => $user->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => [
            'user_id' => $otherUser->id,
            'username' => 'creator',
            'message' => 'Followed you',
        ],
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    Notification::query()->create([
        'notification_type_id' => $types['sales']->id,
        'user_id' => $otherUser->id,
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'drop_title' => 'Other user drop',
        ],
    ]);

    return [
        'user' => $user,
        'other_user' => $otherUser,
    ];
}

test('authenticated users can list paginated notifications', function () {
    $fixture = createNotificationsListFixture();

    $response = $this->actingAs($fixture['user'], 'sanctum')
        ->getJson('/api/notifications?per_page=2');

    $types = collect($response->json('data'))->pluck('type')->all();

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')

        ->assertJsonPath('next_page', 2);

    expect($types)->toHaveCount(2)
        ->and(array_diff($types, ['sales', 'withdraw', 'tracking_order', 'friend_request', 'followers']))->toBe([]);
});

test('authenticated users can filter notifications by types', function () {
    $fixture = createNotificationsListFixture();

    $response = $this->actingAs($fixture['user'], 'sanctum')
        ->getJson('/api/notifications?types[]=tracking_order&types[]=friend_request');

    $types = collect($response->json('data'))->pluck('type')->all();

    $response
        ->assertOk()
        ->assertJsonPath('next_page', null)
        ->assertJsonCount(2, 'data');

    sort($types);

    expect($types)->toBe(['friend_request', 'tracking_order']);
});

test('guests cannot list notifications', function () {
    createNotificationsListFixture();

    $this->getJson('/api/notifications')->assertUnauthorized();
});
