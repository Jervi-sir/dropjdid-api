<?php

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page from stores management', function () {
    $response = $this->get(route('admin.stores.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the stores management page and see password_plaintext', function () {
    $user = User::factory()->create();

    $store = Store::factory()->create([
        'store_name' => 'Tech Gadgets Store',
        'password_plaintext' => 'store-plaintext-pass-123',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('admin.stores.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/stores/list')
        ->has('stores.data', 1)
        ->where('stores.data.0.password_plaintext', 'store-plaintext-pass-123')
    );
});

test('authenticated users can view store details and see password_plaintext', function () {
    $user = User::factory()->create();

    $store = Store::factory()->create([
        'store_name' => 'Apparel Fashion Hub',
        'password_plaintext' => 'apparel-plaintext-pass',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('admin.stores.show', $store));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/stores/show')
        ->where('store.password_plaintext', 'apparel-plaintext-pass')
    );
});

test('authenticated users can update store status and verification', function () {
    $user = User::factory()->create();

    $store = Store::factory()->create([
        'status' => Store::STATUS_PENDING,
        'is_verified' => false,
    ]);

    $this->actingAs($user);

    $response = $this->put(route('admin.stores.update', $store), [
        'status' => 'active',
        'is_verified' => true,
    ]);

    $response->assertRedirect();

    $freshStore = $store->fresh();
    expect($freshStore->status)->toBe(Store::STATUS_ACTIVE);
    expect($freshStore->is_verified)->toBeTrue();
});
