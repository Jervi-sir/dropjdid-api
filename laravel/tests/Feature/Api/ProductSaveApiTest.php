<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Role;
use App\Models\SavedProduct;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createProductSaveFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $owner = User::query()->create([
        'role_id' => $role->id,
        'username' => 'owner',
        'phone_number' => '0551000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0551000002',
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

    $product = Product::query()->create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'gender_id' => $gender->id,
        'quality_id' => $quality->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Product 1',
        'status' => 'published',
    ]);

    SavedProduct::query()->create([
        'user_id' => $viewer->id,
        'product_id' => $product->id,
    ]);

    return [
        'product' => $product,
        'viewer' => $viewer,
    ];
}

test('authenticated users can save and unsave a product', function () {
    $fixture = createProductSaveFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/products/'.$fixture['product']->id.'/save')
        ->assertOk()
        ->assertJsonPath('is_saved', false);

    expect(SavedProduct::query()->where([
        'user_id' => $fixture['viewer']->id,
        'product_id' => $fixture['product']->id,
    ])->exists())->toBeFalse();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/products/'.$fixture['product']->id.'/save')
        ->assertOk()
        ->assertJsonPath('is_saved', true);

    expect(SavedProduct::query()->where([
        'user_id' => $fixture['viewer']->id,
        'product_id' => $fixture['product']->id,
    ])->exists())->toBeTrue();
});
