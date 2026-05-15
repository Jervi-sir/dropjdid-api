<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('users can attach roles through the user roles pivot table', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'relation-test-user',
        'phone_number' => '0550000001',
        'password' => Hash::make('password'),
    ]);

    $user->roles()->attach($role->id);

    expect($user->fresh()->roles->pluck('id'))->toContain($role->id);

    $this->assertDatabaseHas('user_roles', [
        'user_id' => $user->id,
        'role_id' => $role->id,
    ]);
});
