<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can search users with pagination', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'casual_anna',
        'phone_number' => '0440000001',
        'password' => Hash::make('password123'),
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'casual_jane',
        'phone_number' => '0440000002',
        'password' => Hash::make('password123'),
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'formal_john',
        'phone_number' => '0440000003',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->getJson('/api/users/search?query=casual&per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'casual_anna')
        ->assertJsonPath('next_page', 2);
});

test('clients can search people from the search api endpoint', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'sab_people_one',
        'phone_number' => '0440000004',
        'password' => Hash::make('password123'),
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'sab_people_two',
        'phone_number' => '0440000005',
        'password' => Hash::make('password123'),
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'other_person',
        'phone_number' => '0440000006',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->getJson('/api/search/people?query=sab&per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'sab_people_two')
        ->assertJsonPath('next_page', 2);
});
