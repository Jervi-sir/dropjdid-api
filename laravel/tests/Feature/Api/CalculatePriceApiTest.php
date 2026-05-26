<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can calculate product price breakdown without a creator', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0667000001',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Owner Store',
        'status' => 'active',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/sgm/stores/{$store->id}/products/calculate-price", [
            'original_price' => 1000,
        ])
        ->assertOk()
        ->assertJson([
            'original_price' => 1000,
            'show_price' => 1300,
            'store_price' => 1000,
            'octaprize_share' => 300,
            'creator_share' => 0,
            'octaprize_after_creator' => 300,
        ]);
});

test('authenticated users can calculate product price breakdown with a creator', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0667000001',
        'password' => Hash::make('password123'),
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator_user',
        'phone_number' => '0667000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Owner Store',
        'status' => 'active',
    ]);

    // Test with creator_id
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/sgm/stores/{$store->id}/products/calculate-price", [
            'original_price' => 1000,
            'creator_id' => $creator->id,
        ])
        ->assertOk()
        ->assertJson([
            'original_price' => 1000,
            'show_price' => 1300,
            'store_price' => 1000,
            'octaprize_share' => 300,
            'creator_share' => 150,
            'octaprize_after_creator' => 150,
        ]);

    // Test with has_creator boolean
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/sgm/stores/{$store->id}/products/calculate-price", [
            'original_price' => 1000,
            'has_creator' => true,
        ])
        ->assertOk()
        ->assertJson([
            'original_price' => 1000,
            'show_price' => 1300,
            'store_price' => 1000,
            'octaprize_share' => 300,
            'creator_share' => 150,
            'octaprize_after_creator' => 150,
        ]);
});

test('price calculation requires valid original_price', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0667000001',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Owner Store',
        'status' => 'active',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/sgm/stores/{$store->id}/products/calculate-price", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['original_price']);
});
