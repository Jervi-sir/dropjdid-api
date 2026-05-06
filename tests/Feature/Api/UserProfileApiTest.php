<?php

use App\Models\Category;
use App\Models\Contact;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\DropImage;
use App\Models\Friendship;
use App\Models\Gender;
use App\Models\LikedDrop;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\SocialPlatform;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createUserProfileFixture(): array
{
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0990000001',
        'password' => Hash::make('password123'),
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0990000002',
        'password' => Hash::make('password123'),
    ]);

    $socialPlatform = SocialPlatform::query()->create([
        'code' => 'instagram',
        'en' => 'Instagram',
    ]);

    Contact::query()->create([
        'user_id' => $creator->id,
        'social_platform_id' => $socialPlatform->id,
        'url' => 'https://instagram.com/creator',
    ]);

    Friendship::query()->create([
        'sender_id' => $creator->id,
        'receiver_id' => $viewer->id,
        'status' => 'pending',
    ]);

    CreatorFollower::query()->create([
        'user_id' => $viewer->id,
        'creator_id' => $creator->id,
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
        'store_name' => 'Creator store',
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
        'creator_id' => $creator->id,
        'title' => 'Creator drop',
        'status' => 'published',
    ]);

    DropImage::query()->create([
        'drop_id' => $drop->id,
        'image' => 'drops/1.jpg',
        'sort_order' => 0,
        'is_main' => true,
    ]);

    $drop->products()->attach($product->id, ['drop_price' => 99.99]);

    LikedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $drop->id,
    ]);

    SavedDrop::query()->create([
        'user_id' => $viewer->id,
        'drop_id' => $drop->id,
    ]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    return [
        'viewer' => $viewer,
        'creator' => $creator,
        'drop' => $drop,
        'socialPlatform' => $socialPlatform,
    ];
}

test('authenticated users can fetch a profile with friendship and follow state', function () {
    $fixture = createUserProfileFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/users/'.$fixture['creator']->id)
        ->assertOk()
        ->assertJsonPath('data.id', $fixture['creator']->id)
        ->assertJsonPath('data.username', 'creator')
        ->assertJsonPath('data.friend_status', 'request_received')
        ->assertJsonPath('data.friend_request.type', 'incoming')
        ->assertJsonPath('data.is_following', true)
        ->assertJsonPath('data.contacts.0.platform.name', 'Instagram');
});

test('clients can fetch creator drops for a user', function () {
    $fixture = createUserProfileFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/users/'.$fixture['creator']->id.'/creator-drops?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $fixture['drop']->id)
        ->assertJsonPath('data.0.is_liked', true)
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('data.0.products.0.is_saved', true)
        ->assertJsonPath('next_page', null);
});
