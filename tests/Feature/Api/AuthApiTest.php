<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('users can register through the api and receive a bearer token', function () {
    $response = $this->postJson('/api/auth/register', [
        'username' => 'Amine',
        'phone_number' => '0550000001',
        'password' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'username', 'phone_number']])
        ->assertJsonPath('user.username', 'amine');

    expect(User::query()->where('username', 'amine')->exists())->toBeTrue();
    expect(Role::query()->where('code', 'user')->exists())->toBeTrue();
});

test('clients can check whether a username is available', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->getJson('/api/auth/username-availability?username=amine')
        ->assertOk()
        ->assertExactJson(['available' => false]);

    $this->getJson('/api/auth/username-availability?username=amine2')
        ->assertOk()
        ->assertExactJson(['available' => true]);
});

test('users can login and access their api profile', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'username' => 'amine',
        'password' => 'password123',
    ]);

    $token = $loginResponse->json('token');

    $loginResponse
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('user.username', 'amine');
});

test('users can request a password reset link through the api', function () {
    Notification::fake();

    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'username' => 'amine',
    ])
        ->assertOk()
        ->assertJsonStructure(['message']);
});
