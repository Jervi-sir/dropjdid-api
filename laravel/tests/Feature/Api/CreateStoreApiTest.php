<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can create a store with wilaya and password', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $wilaya = Wilaya::query()->create([
        'code' => 'algiers',
        'number' => '16',
        'en' => 'Algiers',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0777000001',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/stores', [
            'wilaya_id' => $wilaya->id,
            'store_name' => 'My New Store',
            'phone_number' => '0555000001',
            'password' => 'storepass123',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.store_name', 'My New Store')
        ->assertJsonPath('data.phone_number', '0555000001')
        ->assertJsonPath('data.wilaya_id', $wilaya->id)
        ->assertJsonPath('data.status', 'pending');

    $store = Store::query()->first();

    expect($store)->not->toBeNull()
        ->and($store?->user_id)->toBe($user->id)
        ->and($store?->password !== 'storepass123')->toBeTrue()
        ->and(Hash::check('storepass123', $store?->password ?? ''))->toBeTrue();
});
