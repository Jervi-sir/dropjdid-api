<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can list their stores with next page', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0661000001',
        'password' => Hash::make('password123'),
    ]);

    $otherUser = User::query()->create([
        'role_id' => $role->id,
        'username' => 'other',
        'phone_number' => '0661000002',
        'password' => Hash::make('password123'),
    ]);

    Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Store One',
        'status' => 'active',
    ]);

    $latestStore = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Store Two',
        'status' => 'pending',
    ]);

    Store::query()->create([
        'user_id' => $otherUser->id,
        'store_name' => 'Other Store',
        'status' => 'active',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/sgm/stores?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $latestStore->id)
        ->assertJsonPath('data.0.store_name', 'Store Two')
        ->assertJsonPath('data.0.status.code', 'pending')
        ->assertJsonPath('next_page', 2);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/sgm/stores?per_page=10')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('next_page', null);
});
