<?php

use App\Models\CreatorFollower;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can list followed creators with pagination and search', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0880000001',
        'password' => Hash::make('password123'),
    ]);

    $creatorOne = User::query()->create([
        'role_id' => $role->id,
        'username' => 'alpha',
        'phone_number' => '0880000002',
        'password' => Hash::make('password123'),
    ]);

    $creatorTwo = User::query()->create([
        'role_id' => $role->id,
        'username' => 'bravo',
        'phone_number' => '0880000003',
        'password' => Hash::make('password123'),
    ]);

    $otherUser = User::query()->create([
        'role_id' => $role->id,
        'username' => 'otheruser',
        'phone_number' => '0880000004',
        'password' => Hash::make('password123'),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $user->id,
        'creator_id' => $creatorOne->id,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $user->id,
        'creator_id' => $creatorTwo->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $otherUser->id,
        'creator_id' => $creatorOne->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/creators/my-following?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.creator_id', $creatorTwo->id)
        ->assertJsonPath('next_page', 2);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/creators/my-following?search=alp')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.creator_id', $creatorOne->id)
        ->assertJsonPath('next_page', null);
});
