<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('settings my profile returns user sections for a non creator without stores', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'basic_user',
        'phone_number' => '0550000001',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/settings/my-profile')
        ->assertOk()
        ->assertJsonPath('data.sections.0.code', 'essentials')
        ->assertJsonPath('data.sections.0.items.0.code', 'friends')
        ->assertJsonPath('data.sections.1.code', 'creator-land')
        ->assertJsonPath('data.sections.1.items.0.code', 'become-creator')
        ->assertJsonMissingPath('data.sections.2.code');
});

test('settings my profile returns creator and sgm sections when applicable', function () {
    $role = Role::query()->create(['code' => 'creator', 'en' => 'Creator']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator_user',
        'phone_number' => '0550000002',
        'password' => Hash::make('password123'),
    ]);

    Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Creator Store',
        'status' => 'active',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/settings/my-profile')
        ->assertOk()
        ->assertJsonPath('data.sections.1.code', 'creator-land')
        ->assertJsonPath('data.sections.1.items.0.code', 'followers')
        ->assertJsonPath('data.sections.1.items.1.code', 'affiliate-library')
        ->assertJsonPath('data.sections.1.items.2.code', 'my-drops')
        ->assertJsonPath('data.sections.1.items.3.code', 'balance')
        ->assertJsonPath('data.sections.2.code', 'sgm')
        ->assertJsonPath('data.sections.2.items.0.code', 'stores')
        ->assertJsonPath('data.sections.2.items.0.count', 1)
        ->assertJsonPath('data.sections.2.items.1.code', 'learning-updates');
});

test('settings my profile can be updated', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'old_user',
        'phone_number' => '0550000003',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/settings/my-profile', [
            'full_name' => 'New Full Name',
            'username' => 'New_User',
            'phone_number' => '0661112233',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Profile updated successfully.')
        ->assertJsonPath('data.full_name', 'New Full Name')
        ->assertJsonPath('data.username', 'new_user')
        ->assertJsonPath('data.phone_number', '0661112233');

    expect($user->fresh()?->full_name)->toBe('New Full Name');
    expect($user->fresh()?->username)->toBe('new_user');
    expect($user->fresh()?->phone_number)->toBe('0661112233');
});

test('settings my profile full name can be updated independently', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'full_name' => 'Old Name',
        'username' => 'profile_user',
        'phone_number' => '0550000006',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/settings/my-profile', [
            'full_name' => 'Updated Name',
        ])
        ->assertOk()
        ->assertJsonPath('data.full_name', 'Updated Name')
        ->assertJsonPath('data.username', 'profile_user')
        ->assertJsonPath('data.phone_number', '0550000006');

    expect($user->fresh()?->full_name)->toBe('Updated Name');
});

test('settings my profile update validates unique username and phone number', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'first_user',
        'phone_number' => '0550000004',
        'password' => Hash::make('password123'),
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'taken_user',
        'phone_number' => '0550000005',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/settings/my-profile', [
            'username' => 'taken_user',
            'phone_number' => '0550000005',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'phone_number']);
});
