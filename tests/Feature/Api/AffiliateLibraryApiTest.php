<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can fetch affiliate library with saved online products first', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'affiliate-owner',
        'phone_number' => '0555666666',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'affiliate-viewer',
        'phone_number' => '0555666667',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Affiliate store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'bags', 'en' => 'Bags']);
    $gender = Gender::query()->create(['code' => 'women', 'en' => 'Women']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $onlinePaymentMethod = PaymentMethod::query()->create(['code' => PaymentMethod::ONLINE, 'en' => 'Online']);
    $codPaymentMethod = PaymentMethod::query()->create(['code' => PaymentMethod::COD, 'en' => 'Cash']);

    $savedOnlineProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $onlinePaymentMethod->id,
        'name' => 'Saved online product',
        'show_price' => 3000,
        'status' => 'published',
    ]);

    $otherOnlineProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $onlinePaymentMethod->id,
        'name' => 'Other online product',
        'show_price' => 4500,
        'status' => 'published',
    ]);

    Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $codPaymentMethod->id,
        'name' => 'Cash product',
        'show_price' => 9999,
        'status' => 'published',
    ]);

    Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $onlinePaymentMethod->id,
        'name' => 'Draft online product',
        'show_price' => 9998,
        'status' => 'draft',
    ]);

    foreach ([$savedOnlineProduct, $otherOnlineProduct] as $product) {
        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/'.$product->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);
    }

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $savedOnlineProduct->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/affiliate-library?saved_per_page=10&products_per_page=10');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.type', 'saved_products')
        ->assertJsonPath('data.0.label', 'Saved products')
        ->assertJsonCount(1, 'data.0.products')
        ->assertJsonPath('data.0.products.0.id', $savedOnlineProduct->id)
        ->assertJsonPath('data.0.products.0.is_saved', true)
        ->assertJsonPath('data.1.type', 'online_products')
        ->assertJsonPath('data.1.label', 'Online products')
        ->assertJsonCount(1, 'data.1.products')
        ->assertJsonPath('data.1.products.0.id', $otherOnlineProduct->id)
        ->assertJsonPath('data.1.products.0.is_saved', false)
        ->assertJsonPath('data.0.next_page', null)
        ->assertJsonPath('data.1.next_page', null);
});

test('guests receive empty saved section and online products only', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'guest-affiliate-owner',
        'phone_number' => '0555666670',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Guest affiliate store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'shoes', 'en' => 'Shoes']);
    $gender = Gender::query()->create(['code' => 'men', 'en' => 'Men']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $onlinePaymentMethod = PaymentMethod::query()->create(['code' => PaymentMethod::ONLINE, 'en' => 'Online']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $onlinePaymentMethod->id,
        'name' => 'Guest online product',
        'show_price' => 2100,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/'.$product->id.'.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $response = $this->getJson('/api/affiliate-library');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonCount(0, 'data.0.products')
        ->assertJsonCount(1, 'data.1.products')
        ->assertJsonPath('data.1.products.0.id', $product->id)
        ->assertJsonPath('data.1.products.0.is_saved', false);
});
