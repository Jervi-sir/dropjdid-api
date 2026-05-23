<?php

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedLabel;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('clients can fetch labels feed', function () {
    [$store, $category, $gender, $quality, $paymentMethod, $viewer] = createLabelFeedProductDependencies('0555222222', 'label-feed-owner');

    Advertisement::query()->create([
        'title' => 'Feed ad',
        'description' => 'Ad description',
        'image' => 'ads/feed.jpg',
        'url' => 'https://example.com/ad',
        'status' => 'active',
        'sort_order' => 1,
    ]);

    $labels = collect(range(1, 5))->map(function (int $index) use ($store, $category, $gender, $quality, $paymentMethod, $viewer) {
        $label = Label::query()->create([
            'code' => 'label-'.$index,
            'en' => 'Label '.$index,
        ]);

        $keyword = Keyword::query()->create([
            'label_id' => $label->id,
            'code' => 'keyword-'.$index,
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'gender_id' => $gender->id,
            'quality_id' => $quality->id,
            'payment_method_id' => $paymentMethod->id,
            'name' => 'Label '.$index.' Product',
            'show_price' => 1000 + $index,
            'status' => Product::STATUS_PUBLISHED,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/label-'.$index.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        ProductKeyword::query()->create([
            'product_id' => $product->id,
            'keyword_id' => $keyword->id,
            'label_id' => $label->id,
        ]);

        if ($index === 1) {
            SavedLabel::query()->create([
                'user_id' => $viewer->id,
                'label_id' => $label->id,
            ]);
        }

        return $label;
    });

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/feeds/labels?per_page=4&products_per_page=1&ads_count=1');

    $response
        ->assertOk()
        ->assertJsonCount(5, 'data') // 4 labels + 1 ad injected after 4th item
        ->assertJsonPath('data.0.type', 'label')
        ->assertJsonPath('data.0.label.id', $labels[0]->id)
        ->assertJsonPath('data.0.label.code', 'label-1')
        ->assertJsonPath('data.0.label.is_liked', true)
        ->assertJsonPath('data.0.nb_likes', 1)
        ->assertJsonCount(1, 'data.0.products')
        ->assertJsonPath('data.0.products.0.id', 1)
        ->assertJsonPath('data.1.type', 'label')
        ->assertJsonPath('data.1.label.id', $labels[1]->id)
        ->assertJsonPath('data.1.label.is_liked', false)
        ->assertJsonPath('data.4.type', 'advertisements')
        ->assertJsonPath('next_page', 2);
});

test('clients can fetch products of a label', function () {
    [$store, $category, $gender, $quality, $paymentMethod, $viewer] = createLabelFeedProductDependencies('0555333333', 'label-products-owner');

    $label = Label::query()->create([
        'code' => 'label-test',
        'en' => 'Label Test',
    ]);

    $keyword = Keyword::query()->create([
        'label_id' => $label->id,
        'code' => 'keyword-test',
    ]);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Label Product 1',
        'show_price' => 1500,
        'status' => Product::STATUS_PUBLISHED,
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/test-1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductKeyword::query()->create([
        'product_id' => $product->id,
        'keyword_id' => $keyword->id,
        'label_id' => $label->id,
    ]);

    LikedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson("/api/feeds/labels/{$label->id}/products?page=1&per_page=10&ads_count=1");

    $response
        ->assertOk()
        ->assertJsonPath('liked_products_count', 1)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', null); // formatProduct doesn't include 'name' in output, let's verify formatProduct keys: type, id, price, image, user, nb_sales, is_saved, payment_method, rejection_reason, status
});

function createLabelFeedProductDependencies(string $phoneNumber, string $username): array
{
    $role = Role::query()->firstOrCreate(['code' => 'user'], ['en' => 'User']);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => $username,
        'phone_number' => $phoneNumber,
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => $username.'-viewer',
        'phone_number' => $phoneNumber.'9',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $owner->id,
        'store_name' => $username.' Store',
        'status' => 'active',
    ]);

    $category = Category::query()->firstOrCreate(['code' => 'shoes'], ['en' => 'Shoes']);
    $gender = Gender::query()->firstOrCreate(['code' => 'women'], ['en' => 'Women']);
    $quality = Quality::query()->firstOrCreate(['code' => 'original'], ['en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->firstOrCreate(['code' => 'cod'], ['en' => 'Cash']);

    return [$store, $category, $gender, $quality, $paymentMethod, $viewer];
}
