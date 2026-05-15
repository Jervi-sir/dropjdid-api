<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can list their store products by status filters', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0667000001',
        'password' => Hash::make('password123'),
    ]);

    $otherUser = User::query()->create([
        'role_id' => $role->id,
        'username' => 'other',
        'phone_number' => '0667000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Owner Store',
        'status' => 'active',
    ]);

    $otherStore = Store::query()->create([
        'user_id' => $otherUser->id,
        'store_name' => 'Other Store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'fashion', 'en' => 'Fashion']);
    $gender = Gender::query()->create(['code' => 'unisex', 'en' => 'Unisex']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'cod', 'en' => 'Cash']);

    $publishedProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Published Product',
        'show_price' => 3200,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $publishedProduct->id,
        'image' => 'products/published.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $draftProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Draft Product',
        'show_price' => 2100,
        'status' => 'draft',
    ]);

    ProductImage::query()->create([
        'product_id' => $draftProduct->id,
        'image' => 'products/draft.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    Product::query()->create([
        'store_id' => $otherStore->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Other Store Product',
        'show_price' => 9999,
        'status' => 'published',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/stores/'.$store->id.'/products?status=draft')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $draftProduct->id)
        ->assertJsonPath('data.0.status', 'draft')
        ->assertJsonPath('next_page', null);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/stores/'.$store->id.'/products?exclude_status=draft')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $publishedProduct->id)
        ->assertJsonPath('data.0.status', 'published')
        ->assertJsonPath('next_page', null);
});
