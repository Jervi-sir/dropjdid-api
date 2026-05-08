<?php

use App\Models\Category;
use App\Models\Drop;
use App\Models\DropImage;
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

test('clients can fetch published drops containing a product', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0660000001',
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
        'show_price' => 7400,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop = Drop::query()->create([
        'creator_id' => $owner->id,
        'title' => 'Published drop',
        'description' => 'Visible drop',
        'status' => 'published',
    ]);

    DropImage::query()->create([
        'drop_id' => $drop->id,
        'image' => 'drops/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop->products()->attach($product->id, ['drop_price' => 99.99]);

    $hiddenDrop = Drop::query()->create([
        'creator_id' => $owner->id,
        'title' => 'Draft drop',
        'status' => 'draft',
    ]);

    $hiddenDrop->products()->attach($product->id, ['drop_price' => 88.88]);

    $response = $this->getJson('/api/products/'.$product->id.'/drops?per_page=10');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('count', 1)
        ->assertJsonPath('data.0.id', $drop->id)
        ->assertJsonPath('data.0.title', 'Published drop')
        ->assertJsonPath('next_page', null);
});
