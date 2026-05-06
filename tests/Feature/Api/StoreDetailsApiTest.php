<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can fetch their store details', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);
    $wilaya = Wilaya::query()->create(['code' => 'algiers', 'number' => '16', 'en' => 'Algiers']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0666000001',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'wilaya_id' => $wilaya->id,
        'store_name' => 'My Store',
        'phone_number' => '0555000001',
        'password' => 'storepass123',
        'status' => 'active',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/stores/'.$store->id)
        ->assertOk()
        ->assertJsonPath('data.id', $store->id)
        ->assertJsonPath('data.store_name', 'My Store')
        ->assertJsonPath('data.wilaya.id', $wilaya->id)
        ->assertJsonPath('data.wilaya.name', 'Algiers');
});

test('authenticated users can update their store details and password', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);
    $oldWilaya = Wilaya::query()->create(['code' => 'algiers', 'number' => '16', 'en' => 'Algiers']);
    $newWilaya = Wilaya::query()->create(['code' => 'oran', 'number' => '31', 'en' => 'Oran']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0666000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'wilaya_id' => $oldWilaya->id,
        'store_name' => 'Old Store',
        'phone_number' => '0555000002',
        'password' => 'oldstorepass',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson('/api/stores/'.$store->id, [
            'wilaya_id' => $newWilaya->id,
            'store_name' => 'New Store',
            'phone_number' => '0555000099',
            'password' => 'newstorepass123',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.store_name', 'New Store')
        ->assertJsonPath('data.phone_number', '0555000099')
        ->assertJsonPath('data.wilaya.id', $newWilaya->id);

    $store->refresh();

    expect($store->store_name)->toBe('New Store')
        ->and($store->phone_number)->toBe('0555000099')
        ->and($store->wilaya_id)->toBe($newWilaya->id)
        ->and(Hash::check('newstorepass123', $store->password))->toBeTrue();
});
