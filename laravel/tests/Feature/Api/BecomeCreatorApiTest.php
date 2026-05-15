<?php

use App\Models\CreatorRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can submit a creator request and fetch its status', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0550000001',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/creators/become')
        ->assertOk()
        ->assertJsonPath('data', null);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/creators/become', [
            'phone_number' => '0550000001',
        ])
        ->assertCreated()
        ->assertJsonPath('data.phone_number', '0550000001')
        ->assertJsonPath('data.status', 'pending');

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/creators/become')
        ->assertOk()
        ->assertJsonPath('data.phone_number', '0550000001')
        ->assertJsonPath('data.status', 'pending');
});

test('authenticated users cannot submit a second pending creator request', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0550000002',
        'password' => Hash::make('password123'),
    ]);

    CreatorRequest::query()->create([
        'user_id' => $user->id,
        'phone_number' => '0550000002',
        'status' => 'pending',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/creators/become', [
            'phone_number' => '0550000002',
        ])
        ->assertStatus(422)
        ->assertJsonPath('data.status', 'pending');
});
