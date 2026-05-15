<?php

use App\Models\Category;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\Friendship;
use App\Models\Gender;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('users can register through the api and receive a bearer token', function () {
    $response = $this->postJson('/api/auth/register', [
        'username' => 'Amine',
        'phone_number' => '0550000001',
        'password' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'username', 'phone_number']])
        ->assertJsonPath('user.username', 'amine');

    expect(User::query()->where('username', 'amine')->exists())->toBeTrue();
    expect(Role::query()->where('code', 'user')->exists())->toBeTrue();
});

test('clients can check whether a username is available', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->getJson('/api/auth/username-availability?username=amine')
        ->assertOk()
        ->assertExactJson(['available' => false]);

    $this->getJson('/api/auth/username-availability?username=amine2')
        ->assertOk()
        ->assertExactJson(['available' => true]);
});

test('users can login and access their api profile', function () {
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'username' => 'amine',
        'password' => 'password123',
    ]);

    $token = $loginResponse->json('token');

    $loginResponse
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('user.username', 'amine');
});

test('users can request a password reset link through the api', function () {
    Notification::fake();

    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'username' => 'amine',
    ])
        ->assertOk()
        ->assertJsonStructure(['message']);
});

test('authenticated users can fetch their profile info and stats', function () {
    $role = Role::query()->create([
        'code' => 'creator',
        'en' => 'Creator',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'amine',
        'email' => 'amine@example.com',
        'password' => Hash::make('password123'),
    ]);

    $friendA = User::query()->create([
        'role_id' => $role->id,
        'username' => 'frienda',
        'email' => 'frienda@example.com',
        'password' => Hash::make('password123'),
    ]);

    $friendB = User::query()->create([
        'role_id' => $role->id,
        'username' => 'friendb',
        'email' => 'friendb@example.com',
        'password' => Hash::make('password123'),
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creatorx',
        'email' => 'creatorx@example.com',
        'password' => Hash::make('password123'),
    ]);

    Friendship::query()->create([
        'sender_id' => $user->id,
        'receiver_id' => $friendA->id,
        'status' => 'accepted',
    ]);

    Friendship::query()->create([
        'sender_id' => $friendB->id,
        'receiver_id' => $user->id,
        'status' => 'accepted',
    ]);

    CreatorFollower::query()->create([
        'user_id' => $user->id,
        'creator_id' => $creator->id,
    ]);

    CreatorFollower::query()->create([
        'user_id' => $friendA->id,
        'creator_id' => $user->id,
    ]);

    $store = Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Store One',
        'status' => 'active',
    ]);

    Store::query()->create([
        'user_id' => $user->id,
        'store_name' => 'Store Two',
        'status' => 'pending',
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
        'name' => 'Saved Product',
        'show_price' => 2500,
        'status' => 'published',
    ]);

    $drop = Drop::query()->create([
        'creator_id' => $user->id,
        'title' => 'Saved Drop',
        'status' => 'published',
    ]);

    SavedProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    SavedDrop::query()->create([
        'user_id' => $user->id,
        'drop_id' => $drop->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/auth/my-profile')
        ->assertOk()
        ->assertJsonPath('data.username', 'amine')
        ->assertJsonPath('data.role.code', 'creator')
        ->assertJsonPath('data.role.name', 'Creator')
        ->assertJsonPath('data.stats.friends', 2)
        ->assertJsonPath('data.stats.followed_creators', 1)
        ->assertJsonPath('data.stats.saved', 2)
        ->assertJsonPath('data.stats.followers', 1)
        ->assertJsonPath('data.stats.stores', 2);
});
