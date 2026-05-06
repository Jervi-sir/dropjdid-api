<?php

use App\Models\Category;
use App\Models\Gender;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createOrdersListFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'buyer',
        'phone_number' => '0558100001',
        'password' => Hash::make('password123'),
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0558100002',
        'password' => Hash::make('password123'),
    ]);

    $store = Store::query()->create([
        'user_id' => $creator->id,
        'store_name' => 'Main store',
        'status' => 'active',
    ]);

    $paymentMethod = PaymentMethod::query()->create(['code' => 'online', 'en' => 'Online']);
    $category = Category::query()->create(['code' => 'shoes', 'en' => 'Shoes']);
    $gender = Gender::query()->create(['code' => 'women', 'en' => 'Women']);
    $quality = Quality::query()->create(['code' => 'original', 'en' => 'Original']);

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

    foreach (range(1, 2) as $index) {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'payment_method_id' => $paymentMethod->id,
            'order_number' => 'ORD-10'.$index,
            'full_name' => 'Buyer',
            'phone_number' => '0558100001',
            'wilaya' => 'Algiers',
            'baladiya' => 'Bab Ezzouar',
            'home_address' => 'Street 1',
            'delivery_fees' => 100,
            'subtotal' => 1000,
            'total' => 1100,
            'status' => $index === 1 ? 'pending' : 'shipped',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Product 1',
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
        ]);
    }

    Order::query()->create([
        'user_id' => $creator->id,
        'store_id' => $store->id,
        'payment_method_id' => $paymentMethod->id,
        'order_number' => 'ORD-999',
        'full_name' => 'Other',
        'phone_number' => '0558100002',
        'wilaya' => 'Algiers',
        'baladiya' => 'Bab Ezzouar',
        'home_address' => 'Street 2',
        'delivery_fees' => 100,
        'subtotal' => 500,
        'total' => 600,
        'status' => 'delivered',
    ]);

    return [
        'user' => $user,
    ];
}

test('authenticated users can list their orders with pagination', function () {
    $fixture = createOrdersListFixture();

    $this->actingAs($fixture['user'], 'sanctum')
        ->getJson('/api/orders?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('next_page', 2)
        ->assertJsonPath('data.0.type', 'online')
        ->assertJsonPath('data.0.image', 'products/1.jpg');
});

test('guests cannot list orders', function () {
    createOrdersListFixture();

    $this->getJson('/api/orders')->assertUnauthorized();
});
