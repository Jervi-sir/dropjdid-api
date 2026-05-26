<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can search published products with pagination', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0330000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0330000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Main store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'shoes', 'en' => 'Shoes']);
    $gender = Gender::query()->create(['code' => 'women', 'en' => 'Women']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'cod', 'en' => 'Cash']);

    $productOne = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Casual shirt',
        'show_price' => 7400,
        'status' => 'published',
    ]);

    $productTwo = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Casual pants',
        'show_price' => 5400,
        'status' => 'published',
    ]);

    Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Formal suit',
        'show_price' => 9400,
        'status' => 'published',
    ]);

    foreach ([$productOne, $productTwo] as $product) {
        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/'.$product->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);
    }

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $productOne->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/products/search?query=Casual&per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('next_page', 2);
});

test('clients can search products from the search api endpoint', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'search_owner',
        'phone_number' => '0330000003',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'search_viewer',
        'phone_number' => '0330000004',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Search store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'tops', 'en' => 'Tops']);
    $gender = Gender::query()->create(['code' => 'men', 'en' => 'Men']);
    $quality = Quality::query()->create(['code' => 'premium', 'en' => 'Premium']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'card', 'en' => 'Card']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Sab casual shirt',
        'show_price' => 6100,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/search-endpoint.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/search/products?query=Sab&per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.image', 'products/search-endpoint.jpg')
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('next_page', null);
});

test('clients can search products by keywords and labels', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'taxonomy_owner',
        'phone_number' => '0330000005',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Taxonomy store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'shirts', 'en' => 'Shirts']);
    $gender = Gender::query()->create(['code' => 'unisex', 'en' => 'Unisex']);
    $quality = Quality::query()->create(['code' => 'standard', 'en' => 'Standard']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'card', 'en' => 'Card']);

    $label = Label::query()->create([
        'code' => 'sabata-style',
        'en' => 'Sabata Style',
        'fr' => 'Sabata Style',
        'ar' => 'Sabata Style',
    ]);

    $keyword = Keyword::query()->create([
        'label_id' => $label->id,
        'code' => 'sabata',
    ]);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Plain shirt',
        'show_price' => 6800,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/taxonomy-search.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductKeyword::query()->create([
        'product_id' => $product->id,
        'keyword_id' => $keyword->id,
        'label_id' => $label->id,
    ]);

    $response = $this->getJson('/api/search/products?query=Sabata&per_page=10');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.image', 'products/taxonomy-search.jpg');
});
