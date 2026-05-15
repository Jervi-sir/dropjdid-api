<?php

use App\Models\Category;
use App\Models\Drop;
use App\Models\DropImage;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\LikedDrop;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can search published drops with pagination', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0220000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0220000002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
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

    $dropOne = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Casual vibes',
        'status' => 'published',
    ]);

    $dropTwo = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Casual night',
        'status' => 'published',
    ]);

    Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Formal fit',
        'status' => 'published',
    ]);

    foreach ([$dropOne, $dropTwo] as $drop) {
        DropImage::query()->create([
            'drop_id' => $drop->id,
            'image' => 'drops/'.$drop->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        $drop->products()->attach($product->id, ['drop_price' => 99.99]);
    }

    LikedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $dropOne->id,
    ]);

    SavedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $dropOne->id,
    ]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/drops/search?query=Casual&per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.products.0.is_saved', true)
        ->assertJsonPath('next_page', 2);
});

test('clients can search drops from the search api endpoint', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'search_creator',
        'phone_number' => '0220000003',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'search_viewer',
        'phone_number' => '0220000004',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
        'store_name' => 'Search store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'tops', 'en' => 'Tops']);
    $gender = Gender::query()->create(['code' => 'men', 'en' => 'Men']);
    $quality = Quality::query()->create(['code' => 'premium', 'en' => 'Premium']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'mobile', 'en' => 'Mobile']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Search product',
        'show_price' => 5500,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/search.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Sab fit',
        'status' => 'published',
    ]);

    DropImage::query()->create([
        'drop_id' => $drop->id,
        'image' => 'drops/search.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop->products()->attach($product->id, ['drop_price' => 120.5]);

    SavedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $drop->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/search/drops?query=Sab&per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.type', 'drop')
        ->assertJsonPath('data.0.title', 'Sab fit')
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('data.0.products.0.image', 'products/search.jpg');
});

test('clients can search drops by product labels and keywords', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'taxonomy_creator',
        'phone_number' => '0220000005',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
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
        'show_price' => 6300,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/taxonomy.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductKeyword::query()->create([
        'product_id' => $product->id,
        'keyword_id' => $keyword->id,
        'label_id' => $label->id,
    ]);

    $drop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Minimal fit',
        'status' => 'published',
    ]);

    DropImage::query()->create([
        'drop_id' => $drop->id,
        'image' => 'drops/taxonomy.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop->products()->attach($product->id, ['drop_price' => 98.5]);

    $response = $this->getJson('/api/search/drops?query=Sabata&per_page=10');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Minimal fit')
        ->assertJsonPath('data.0.products.0.image', 'products/taxonomy.jpg');
});
