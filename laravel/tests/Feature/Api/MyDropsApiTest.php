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

test('authenticated users can list their created drops', function () {
    $role = Role::query()->create([
        'code' => 'creator',
        'en' => 'Creator',
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0991000001',
        'password' => Hash::make('password123'),
    ]);

    $otherUser = User::query()->create([
        'role_id' => $role->id,
        'username' => 'other',
        'phone_number' => '0991000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
        'store_name' => 'Creator Store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'fashion', 'en' => 'Fashion']);
    $gender = Gender::query()->create(['code' => 'unisex', 'en' => 'Unisex']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'cod', 'en' => 'Cash']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 1',
        'show_price' => 3200,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $latestDrop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Latest Drop',
        'status' => 'draft',
    ]);

    DropImage::query()->create([
        'drop_id' => $latestDrop->id,
        'image' => 'drops/latest.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $latestDrop->products()->attach($product->id, ['drop_price' => 99.99]);

    Drop::query()->create([
        'creator_id' => $otherUser->id,
        'title' => 'Other Drop',
        'status' => 'published',
    ]);

    $this->actingAs($creator, 'sanctum')
        ->getJson('/api/creators/my-drops?per_page=10')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Latest Drop')
        ->assertJsonPath('data.0.status', 'draft')
        ->assertJsonPath('data.0.creator.id', $creator->id)
        ->assertJsonPath('data.0.products.0.id', $product->id)
        ->assertJsonPath('next_page', null);
});
