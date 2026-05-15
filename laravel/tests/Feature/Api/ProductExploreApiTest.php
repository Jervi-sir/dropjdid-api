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
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can fetch paginated explore labels with initial products', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);
    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0555000001',
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

    $labelOne = Label::query()->create(['code' => 'season', 'en' => 'Season']);
    $labelTwo = Label::query()->create(['code' => 'pattern', 'en' => 'Pattern']);
    $keywordOne = Keyword::query()->create(['label_id' => $labelOne->id, 'code' => 'summer']);
    $keywordTwo = Keyword::query()->create(['label_id' => $labelTwo->id, 'code' => 'striped']);

    $productOne = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 1',
        'show_price' => 1000,
        'status' => 'published',
    ]);

    $productTwo = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 2',
        'show_price' => 1200,
        'status' => 'published',
    ]);

    foreach ([[$productOne, $labelOne, $keywordOne], [$productTwo, $labelTwo, $keywordTwo]] as [$product, $label, $keyword]) {
        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/'.$product->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        ProductKeyword::query()->create([
            'product_id' => $product->id,
            'keyword_id' => $keyword->id,
            'label_id' => $label->id,
        ]);
    }

    $response = $this->getJson('/api/products/explore?per_page=1&products_per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $labelOne->id)
        ->assertJsonPath('data.0.products.data.0.id', $productOne->id)
        ->assertJsonPath('next_page', 2);
});

test('clients can fetch more products for a label', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);
    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0555000002',
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

    $label = Label::query()->create(['code' => 'season', 'en' => 'Season']);
    $keyword = Keyword::query()->create(['label_id' => $label->id, 'code' => 'summer']);

    $productIds = [];
    foreach ([1, 2] as $index) {
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'gender_id' => $gender->id,
            'quality_id' => $quality->id,
            'payment_method_id' => $paymentMethod->id,
            'name' => 'Product '.$index,
            'show_price' => 1000 + $index,
            'status' => 'published',
        ]);

        $productIds[] = $product->id;

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/'.$product->id.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        ProductKeyword::query()->create([
            'product_id' => $product->id,
            'keyword_id' => $keyword->id,
            'label_id' => $label->id,
        ]);
    }

    $response = $this->getJson('/api/products/explore?label_id='.$label->id.'&products_page=1&products_per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.next_page', 2);
});
