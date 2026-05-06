<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\ProductVariant;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedProduct;
use App\Models\Size;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createProductDetailsFixture(): array
{
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0770000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0770000002',
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
    $size = Size::query()->create([
        'category_id' => $category->id,
        'code' => 'M',
        'type' => 'alpha',
        'en' => 'Medium',
        'fr' => 'Moyen',
        'ar' => 'متوسط',
    ]);

    $labelOne = Label::query()->create(['code' => 'season', 'en' => 'Season', 'fr' => 'Season', 'ar' => 'Season']);
    $labelTwo = Label::query()->create(['code' => 'pattern', 'en' => 'Pattern', 'fr' => 'Pattern', 'ar' => 'Pattern']);

    $keywordOne = Keyword::query()->create(['label_id' => $labelOne->id, 'code' => 'summer']);
    $keywordTwo = Keyword::query()->create(['label_id' => $labelTwo->id, 'code' => 'striped']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 1',
        'description' => 'Visible product',
        'show_price' => 7400,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductVariant::query()->create([
        'product_id' => $product->id,
        'size_id' => $size->id,
        'quantity' => 2,
    ]);

    ProductKeyword::query()->create(['product_id' => $product->id, 'keyword_id' => $keywordOne->id, 'label_id' => $labelOne->id]);
    ProductKeyword::query()->create(['product_id' => $product->id, 'keyword_id' => $keywordTwo->id, 'label_id' => $labelTwo->id]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    LikedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    $sameLabelProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 2',
        'show_price' => 3500,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $sameLabelProduct->id,
        'image' => 'products/2.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductKeyword::query()->create(['product_id' => $sameLabelProduct->id, 'keyword_id' => $keywordOne->id, 'label_id' => $labelOne->id]);

    $randomProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 3',
        'show_price' => 2800,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $randomProduct->id,
        'image' => 'products/3.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    return [
        'viewer' => $viewer,
        'product' => $product,
        'labelOne' => $labelOne,
        'sameLabelProduct' => $sameLabelProduct,
    ];
}

test('clients can fetch a product by id', function () {
    $fixture = createProductDetailsFixture();

    $response = $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/products/'.$fixture['product']->id);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $fixture['product']->id)
        ->assertJsonPath('data.title', 'Product 1')
        ->assertJsonPath('data.images.0', 'products/1.jpg')
        ->assertJsonPath('data.price', 7400)
        ->assertJsonPath('data.nb_likes', 1)
        ->assertJsonPath('data.is_liked', true)
        ->assertJsonPath('data.available_sizes.0', 'M')
        ->assertJsonPath('data.is_saved', true)
        ->assertJsonPath('data.labels.0.id', $fixture['labelOne']->id);
});

test('clients can fetch random product suggestions with next page', function () {
    $fixture = createProductDetailsFixture();

    $response = $this->getJson('/api/products/'.$fixture['product']->id.'/suggestions?per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('next_page', 2);
});

test('clients can fetch same label product suggestions', function () {
    $fixture = createProductDetailsFixture();

    $response = $this->getJson('/api/products/'.$fixture['product']->id.'/suggestions?label_id='.$fixture['labelOne']->id);

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $fixture['sameLabelProduct']->id)
        ->assertJsonPath('next_page', null);
});

test('authenticated users can like and unlike a product', function () {
    $fixture = createProductDetailsFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/products/'.$fixture['product']->id.'/like')
        ->assertOk()
        ->assertJsonPath('is_liked', false)
        ->assertJsonPath('nb_likes', 0);

    expect(LikedProduct::query()->where([
        'user_id' => $fixture['viewer']->id,
        'product_id' => $fixture['product']->id,
    ])->exists())->toBeFalse();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/products/'.$fixture['product']->id.'/like')
        ->assertOk()
        ->assertJsonPath('is_liked', true)
        ->assertJsonPath('nb_likes', 1);

    expect(LikedProduct::query()->where([
        'user_id' => $fixture['viewer']->id,
        'product_id' => $fixture['product']->id,
    ])->exists())->toBeTrue();
});
