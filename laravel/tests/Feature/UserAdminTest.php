<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page from users management', function () {
    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the users management page and see password_plaintext', function () {
    $role = Role::query()->create([
        'code' => Role::USER,
        'en' => 'User',
    ]);

    $user = User::factory()->create([
        'password_plaintext' => 'plaintext-password-123',
    ]);
    $user->roles()->attach($role->id);

    $this->actingAs($user);

    $response = $this->get(route('admin.users.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/list')
        ->has('users.data', 1)
        ->where('users.data.0.password_plaintext', 'plaintext-password-123')
    );
});

test('authenticated users can view user details and see password_plaintext', function () {
    $role = Role::query()->create([
        'code' => Role::USER,
        'en' => 'User',
    ]);

    $user = User::factory()->create([
        'password_plaintext' => 'secure-plaintext-pass',
    ]);
    $user->roles()->attach($role->id);

    $targetUser = User::factory()->create([
        'password_plaintext' => 'target-plaintext-pass',
    ]);
    $targetUser->roles()->attach($role->id);

    $this->actingAs($user);

    $response = $this->get(route('admin.users.show', $targetUser));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/show')
        ->where('user.password_plaintext', 'target-plaintext-pass')
    );
});

test('authenticated users can update roles and active status of user', function () {
    $role1 = Role::query()->create([
        'code' => Role::USER,
        'en' => 'User',
    ]);

    $role2 = Role::query()->create([
        'code' => Role::CREATOR,
        'en' => 'Creator',
    ]);

    $admin = User::factory()->create();
    $admin->roles()->attach($role1->id);

    $targetUser = User::factory()->create([
        'is_active' => true,
    ]);
    $targetUser->roles()->attach($role1->id);

    $this->actingAs($admin);

    $response = $this->put(route('admin.users.update', $targetUser), [
        'is_active' => false,
        'role_ids' => [$role2->id],
    ]);

    $response->assertRedirect();

    $freshUser = $targetUser->fresh();
    expect($freshUser->is_active)->toBeFalse();
    expect($freshUser->roles->pluck('id'))->toContain($role2->id);
    expect($freshUser->roles->pluck('id'))->not->toContain($role1->id);
});
