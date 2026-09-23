<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('people reposted products returns list of reposted products', function () {
    $person = User::factory()->create();
    $viewer = User::factory()->create();
    $storeOwner = User::factory()->create();

    $store = Store::create([
        'user_id' => $storeOwner->id,
        'name' => 'Fashion Store',
        'store_status' => 'active',
    ]);

    $product1 = Product::create([
        'store_id' => $store->id,
        'name' => 'First Reposted Product',
        'price_original' => 5000,
        'price_shown' => 4000,
        'product_status' => 'published',
    ]);

    $product2 = Product::create([
        'store_id' => $store->id,
        'name' => 'Second Reposted Product',
        'price_original' => 2500,
        'price_shown' => 2500,
        'product_status' => 'published',
    ]);

    // Person reposts product1 and product2
    UserInteraction::create([
        'user_id' => $person->id,
        'type' => UserInteraction::TYPE_REPOST,
        'target_type' => UserInteraction::TARGET_PRODUCT,
        'target_id' => $product1->id,
        'meta' => ['quote' => 'Great product quality!'],
    ]);

    UserInteraction::create([
        'user_id' => $person->id,
        'type' => UserInteraction::TYPE_REPOST,
        'target_type' => UserInteraction::TARGET_PRODUCT,
        'target_id' => $product2->id,
    ]);

    // Viewer has saved product1
    $product1->savedUsers()->attach($viewer->id);

    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/people/{$person->id}/reposted-products");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $product2->id)
        ->assertJsonPath('data.0.text', 'Second Reposted Product')
        ->assertJsonPath('data.1.id', $product1->id)
        ->assertJsonPath('data.1.text', 'First Reposted Product')
        ->assertJsonPath('data.1.quote', 'Great product quality!')
        ->assertJsonPath('data.1.is_saved', true)
        ->assertJsonPath('data.1.save.is_saved', true)
        ->assertJsonPath('data.1.save.nb_save', 1);
});

test('people reposted products returns 404 for non-existing user', function () {
    $response = $this->getJson('/api/people/999999/reposted-products');
    $response->assertNotFound();
});
