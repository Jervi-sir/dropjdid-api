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
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can fetch paginated saved products', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0660000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0660000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => 'Saved Store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'shoes', 'en' => 'Shoes']);
    $gender = Gender::query()->create(['code' => 'women', 'en' => 'Women']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'cod', 'en' => 'Cash']);

    $firstProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Saved Product 1',
        'show_price' => 7400,
        'status' => 'published',
    ]);

    $secondProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Saved Product 2',
        'show_price' => 5400,
        'status' => 'published',
    ]);

    foreach ([$firstProduct, $secondProduct] as $product) {
        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/'.$product->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);
    }

    SavedProduct::query()->create(['user_id' => $viewer->id, 'product_id' => $firstProduct->id]);
    SavedProduct::query()->create(['user_id' => $viewer->id, 'product_id' => $secondProduct->id]);

    $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/settings/saved-products?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $secondProduct->id)
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('next_page', 2);
});

test('authenticated users can fetch paginated saved drops', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0660000003',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer_drop',
        'phone_number' => '0660000004',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
        'store_name' => 'Drop Store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'bags', 'en' => 'Bags']);
    $gender = Gender::query()->create(['code' => 'unisex', 'en' => 'Unisex']);
    $quality = Quality::query()->create(['code' => 'premium', 'en' => 'Premium']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'online', 'en' => 'Online']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Drop Product',
        'show_price' => 8800,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/drop.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $firstDrop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Saved Drop 1',
        'status' => 'published',
    ]);

    $secondDrop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Saved Drop 2',
        'status' => 'published',
    ]);

    foreach ([$firstDrop, $secondDrop] as $drop) {
        DropImage::query()->create([
            'drop_id' => $drop->id,
            'image' => 'drops/'.$drop->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        $drop->products()->attach($product->id, ['drop_price' => 99.99]);
    }

    SavedDrop::query()->create(['user_id' => $viewer->id, 'drop_id' => $firstDrop->id]);
    SavedDrop::query()->create(['user_id' => $viewer->id, 'drop_id' => $secondDrop->id]);

    $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/settings/saved-drops?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $secondDrop->id)
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('data.0.products.0.is_saved', false)
        ->assertJsonPath('next_page', 2);
});
