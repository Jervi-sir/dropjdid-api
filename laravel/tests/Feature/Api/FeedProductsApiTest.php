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

test('clients can fetch the mixed products feed with visible and queued label sections', function () {
    [$store, $category, $gender, $quality, $paymentMethod, $viewer] = createFeedProductDependencies('0555111111', 'feed-owner');

    Advertisement::query()->create([
        'title' => 'Feed ad',
        'description' => 'Ad description',
        'image' => 'ads/feed.jpg',
        'url' => 'https://example.com/ad',
        'status' => 'active',
        'sort_order' => 1,
    ]);

    $labels = collect(range(1, 8))->map(function (int $index) use ($store, $category, $gender, $quality, $paymentMethod, $viewer) {
        $label = Label::query()->create([
            'code' => 'label-'.$index,
            'en' => 'Label '.$index,
        ]);

        $keyword = Keyword::query()->create([
            'label_id' => $label->id,
            'code' => 'keyword-'.$index,
        ]);

        foreach ([1, 2] as $productIndex) {
            $product = Product::query()->create([
                'store_id' => $store->id,
                'category_id' => $category->id,
                'gender_id' => $gender->id,
                'quality_id' => $quality->id,
                'payment_method_id' => $paymentMethod->id,
                'name' => 'Label '.$index.' Product '.$productIndex,
                'show_price' => ($index * 1000) + $productIndex,
                'status' => 'published',
            ]);

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image' => 'products/label-'.$index.'-'.$productIndex.'.jpg',
                'sort_order' => 0,
                'is_main' => true,
            ]);

            ProductKeyword::query()->create([
                'product_id' => $product->id,
                'keyword_id' => $keyword->id,
                'label_id' => $label->id,
            ]);

            if ($productIndex === 1 && $index <= 2) {
                LikedProduct::query()->create([
                    'user_id' => $viewer->id,
                    'product_id' => $product->id,
                ]);

                SavedLabel::query()->create([
                    'user_id' => $viewer->id,
                    'label_id' => $label->id,
                ]);
            }
        }

        return $label;
    });

    $randomProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Random Product',
        'show_price' => 9999,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $randomProduct->id,
        'image' => 'products/random.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/feeds/products?per_page=4&products_per_page=1&ads_count=1');

    $response
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.type', 'label')
        ->assertJsonPath('data.0.label.id', $labels[0]->id)
        ->assertJsonPath('data.0.label.code', 'label-1')
        ->assertJsonPath('data.0.label.en', 'Label 1')
        ->assertJsonPath('data.0.products.0.id', 2)
        ->assertJsonPath('data.0.nb_likes', 1)
        ->assertJsonPath('data.0.next_page', 2)
        ->assertJsonPath('data.1.type', 'label')
        ->assertJsonPath('data.2.type', 'advertisements')
        ->assertJsonCount(1, 'data.2.data')
        ->assertJsonPath('data.2.data.0.title', 'Feed ad')
        ->assertJsonPath('data.3.type', 'label')
        ->assertJsonPath('data.4.type', 'label')
        ->assertJsonPath('data.5.type', 'advertisements')
        ->assertJsonPath('next_page', 2);
});

test('clients can fetch feed products filtered by label', function () {
    [$store, $category, $gender, $quality, $paymentMethod, $viewer] = createFeedProductDependencies('0555333333', 'label-owner');

    $label = Label::query()->create(['code' => 'season', 'en' => 'Season']);
    $otherLabel = Label::query()->create(['code' => 'style', 'en' => 'Style']);
    $keyword = Keyword::query()->create(['label_id' => $label->id, 'code' => 'summer']);
    $otherKeyword = Keyword::query()->create(['label_id' => $otherLabel->id, 'code' => 'casual']);

    $matchingProducts = collect([1, 2])->map(function (int $index) use ($store, $category, $gender, $quality, $paymentMethod, $label, $keyword) {
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'gender_id' => $gender->id,
            'quality_id' => $quality->id,
            'payment_method_id' => $paymentMethod->id,
            'name' => 'Matching Product '.$index,
            'show_price' => 1000 + $index,
            'status' => 'published',
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/matching-'.$index.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);

        ProductKeyword::query()->create([
            'product_id' => $product->id,
            'keyword_id' => $keyword->id,
            'label_id' => $label->id,
        ]);

        return $product;
    });

    $otherProduct = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Other Product',
        'show_price' => 9999,
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $otherProduct->id,
        'image' => 'products/other.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    ProductKeyword::query()->create([
        'product_id' => $otherProduct->id,
        'keyword_id' => $otherKeyword->id,
        'label_id' => $otherLabel->id,
    ]);

    LikedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $matchingProducts->first()->id,
    ]);

    LikedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $otherProduct->id,
    ]);

    $response = $this->actingAs($viewer, 'sanctum')
        ->getJson('/api/feeds/products?label_id='.$label->id.'&products_page=1&products_per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingProducts->last()->id)
        ->assertJsonPath('data.0.price', 1002)
        ->assertJsonPath('data.0.image', 'products/matching-2.jpg')
        ->assertJsonPath('data.0.user.name', 'label-owner')
        ->assertJsonPath('data.0.is_saved', false)
        ->assertJsonPath('liked_products_count', 1)
        ->assertJsonPath('next_page', 2);
});

test('clients can fetch random products section pagination', function () {
    [$store, $category, $gender, $quality, $paymentMethod] = createFeedProductDependencies('0555444444', 'random-owner');

    foreach ([1, 2] as $index) {
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'gender_id' => $gender->id,
            'quality_id' => $quality->id,
            'payment_method_id' => $paymentMethod->id,
            'name' => 'Random Product '.$index,
            'show_price' => 2300 + $index,
            'status' => 'published',
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/random-'.$index.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);
    }

    $response = $this->getJson('/api/feeds/products?section=random&products_page=1&products_per_page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('next_page', 2);
});

function createFeedProductDependencies(string $phoneNumber, string $username): array
{
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

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
        'store_name' => 'Main store',
        'status' => 'active',
    ]);

    $category = Category::query()->create(['code' => 'shoes', 'en' => 'Shoes']);
    $gender = Gender::query()->create(['code' => 'women', 'en' => 'Women']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);
    $paymentMethod = PaymentMethod::query()->create(['code' => 'cod', 'en' => 'Cash']);

    return [$store, $category, $gender, $quality, $paymentMethod, $viewer];
}
