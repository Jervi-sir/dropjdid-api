<?php

use App\Models\CreatorFollower;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated creators can list their followers with pagination and search', function () {
    $role = Role::query()->create([
        'code' => 'creator',
        'en' => 'Creator',
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0770000001',
        'password' => Hash::make('password123'),
    ]);

    $followerOne = User::query()->create([
        'role_id' => $role->id,
        'username' => 'alpha',
        'phone_number' => '0770000002',
        'password' => Hash::make('password123'),
    ]);

    $followerTwo = User::query()->create([
        'role_id' => $role->id,
        'username' => 'bravo',
        'phone_number' => '0770000003',
        'password' => Hash::make('password123'),
    ]);

    $otherCreator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'othercreator',
        'phone_number' => '0770000004',
        'password' => Hash::make('password123'),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $followerOne->id,
        'creator_id' => $creator->id,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $followerTwo->id,
        'creator_id' => $creator->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    CreatorFollower::query()->create([
        'user_id' => $followerOne->id,
        'creator_id' => $otherCreator->id,
    ]);

    $this->actingAs($creator, 'sanctum')
        ->getJson('/api/creators/my-followers?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user_id', $followerTwo->id)
        ->assertJsonPath('next_page', 2);

    $this->actingAs($creator, 'sanctum')
        ->getJson('/api/creators/my-followers?search=alp')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user_id', $followerOne->id)
        ->assertJsonPath('next_page', null);
});
