<?php

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropImage;
use App\Models\Gender;
use App\Models\LikedDrop;
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

function createDropsListFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0550000001',
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
        'status' => 'published',
    ]);

    ProductImage::query()->create([
        'product_id' => $product->id,
        'image' => 'products/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $publishedDrop = Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Published drop',
        'description' => 'Visible drop',
        'status' => 'published',
    ]);

    DropImage::query()->create([
        'drop_id' => $publishedDrop->id,
        'image' => 'drops/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $publishedDrop->products()->attach($product->id, ['drop_price' => 99.99]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0550000002',
        'password' => Hash::make('password123'),
    ]);

    LikedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $publishedDrop->id,
    ]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    SavedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $publishedDrop->id,
    ]);

    Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Published drop 2',
        'status' => 'published',
    ]);

    Drop::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Draft drop',
        'status' => 'draft',
    ]);

    return [
        'creator' => $creator,
        'drop' => $publishedDrop,
        'product' => $product,
        'viewer' => $viewer,
    ];
}

test('guests can list published drops with products and next page', function () {
    $fixture = createDropsListFixture();

    $response = $this->getJson('/api/drops?per_page=1');

    $response
        ->assertOk()
        ->assertJsonPath('next_page', 2)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Published drop')
        ->assertJsonPath('data.0.images.0', 'drops/1.jpg')
        ->assertJsonPath('data.0.creator.id', $fixture['creator']->id)
        ->assertJsonPath('data.0.creator.name', 'creator')
        ->assertJsonPath('data.0.nb_likes', 1)
        ->assertJsonPath('data.0.is_liked', false)
        ->assertJsonPath('data.0.products.0.id', $fixture['product']->id)
        ->assertJsonPath('data.0.products.0.price', 99.99)
        ->assertJsonPath('data.0.products.0.image', 'products/1.jpg')
        ->assertJsonPath('data.0.products.0.user.id', $fixture['creator']->id)
        ->assertJsonPath('data.0.products.0.user.name', 'creator')
        ->assertJsonPath('data.0.products.0.is_saved', false);
});

test('clients with bearer token get authenticated user on drops list', function () {
    $fixture = createDropsListFixture();
    $token = $fixture['viewer']->createToken('drops-list')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/drops?per_page=1');

    $response
        ->assertOk()
        ->assertJsonPath('next_page', 2)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Published drop')
        ->assertJsonPath('data.0.images.0', 'drops/1.jpg')
        ->assertJsonPath('data.0.creator.id', $fixture['creator']->id)
        ->assertJsonPath('data.0.creator.name', 'creator')
        ->assertJsonPath('data.0.nb_likes', 1)
        ->assertJsonPath('data.0.is_liked', true)
        ->assertJsonPath('data.0.products.0.id', $fixture['product']->id)
        ->assertJsonPath('data.0.products.0.price', 99.99)
        ->assertJsonPath('data.0.products.0.image', 'products/1.jpg')
        ->assertJsonPath('data.0.products.0.user.id', $fixture['creator']->id)
        ->assertJsonPath('data.0.products.0.user.name', 'creator')
        ->assertJsonPath('data.0.products.0.is_saved', true);
});

test('drops list accepts feed filter parameter', function () {
    createDropsListFixture();

    $this->getJson('/api/drops?filter=trending')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'next_page',
        ]);
});

test('active advertisements are injected after every 10 drops', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0550000099',
        'password' => Hash::make('password123'),
    ]);

    for ($index = 1; $index <= 10; $index++) {
        $drop = Drop::query()->create([
            'creator_id' => $creator->id,
            'title' => 'Published drop '.$index,
            'status' => 'published',
        ]);

        DropImage::query()->create([
            'drop_id' => $drop->id,
            'image' => 'drops/'.$index.'.jpg',
            'sort_order' => 0,
            'is_main' => true,
        ]);
    }

    $advertisement = Advertisement::query()->create([
        'title' => 'Feed ad',
        'image' => 'ads/1.jpg',
        'url' => 'https://example.com/ad',
        'status' => 'active',
        'sort_order' => 1,
    ]);

    Advertisement::query()->create([
        'title' => 'Draft ad',
        'image' => 'ads/2.jpg',
        'url' => 'https://example.com/draft',
        'status' => 'draft',
        'sort_order' => 2,
    ]);

    $response = $this->getJson('/api/drops?per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('next_page', null)
        ->assertJsonCount(11, 'data')
        ->assertJsonPath('data.0.type', 'drop')
        ->assertJsonPath('data.9.type', 'drop')
        ->assertJsonPath('data.10.type', 'advertisement')
        ->assertJsonPath('data.10.id', $advertisement->id)
        ->assertJsonPath('data.10.title', 'Feed ad')
        ->assertJsonPath('data.10.image', 'ads/1.jpg')
        ->assertJsonPath('data.10.url', 'https://example.com/ad');
});

test('authenticated users can like and unlike a drop', function () {
    $fixture = createDropsListFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/drops/'.$fixture['drop']->id.'/like')
        ->assertOk()
        ->assertJsonPath('is_liked', false)
        ->assertJsonPath('nb_likes', 0);

    expect(LikedDrop::query()->where([
        'user_id' => $fixture['viewer']->id,
        'drop_id' => $fixture['drop']->id,
    ])->exists())->toBeFalse();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/drops/'.$fixture['drop']->id.'/like')
        ->assertOk()
        ->assertJsonPath('is_liked', true)
        ->assertJsonPath('nb_likes', 1);

    expect(LikedDrop::query()->where([
        'user_id' => $fixture['viewer']->id,
        'drop_id' => $fixture['drop']->id,
    ])->exists())->toBeTrue();
});

test('authenticated users can save and unsave a drop', function () {
    $fixture = createDropsListFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/drops/'.$fixture['drop']->id.'/save')
        ->assertOk()
        ->assertJsonPath('is_saved', false);

    expect(SavedDrop::query()->where([
        'user_id' => $fixture['viewer']->id,
        'drop_id' => $fixture['drop']->id,
    ])->exists())->toBeFalse();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/drops/'.$fixture['drop']->id.'/save')
        ->assertOk()
        ->assertJsonPath('is_saved', true);

    expect(SavedDrop::query()->where([
        'user_id' => $fixture['viewer']->id,
        'drop_id' => $fixture['drop']->id,
    ])->exists())->toBeTrue();
});
