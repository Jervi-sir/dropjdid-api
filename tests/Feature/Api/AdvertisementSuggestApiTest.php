<?php

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Gender;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can fetch paginated random advertisements for a product', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0880000001',
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

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 1',
        'status' => 'published',
    ]);

    Advertisement::query()->create([
        'title' => 'Ad 1',
        'image' => 'ads/1.jpg',
        'url' => 'https://example.com/1',
        'status' => 'active',
        'sort_order' => 1,
    ]);

    Advertisement::query()->create([
        'title' => 'Ad 2',
        'image' => 'ads/2.jpg',
        'url' => 'https://example.com/2',
        'status' => 'active',
        'sort_order' => 2,
    ]);

    Advertisement::query()->create([
        'title' => 'Draft ad',
        'image' => 'ads/3.jpg',
        'url' => 'https://example.com/3',
        'status' => 'draft',
        'sort_order' => 3,
    ]);

    $response = $this->getJson('/api/advertisements/products/'.$product->id.'/suggestions?per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'advertisement')
        ->assertJsonPath('next_page', 2);
});
