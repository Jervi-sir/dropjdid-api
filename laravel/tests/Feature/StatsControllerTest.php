<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('guests are redirected to login from product stats', function () {
    $this->seed(CatalogSeeder::class);

    $owner = User::factory()->create();

    $storeId = DB::table('stores')->insertGetId([
        'user_id' => $owner->id,
        'store_name' => 'Test Store',
        'phone_number' => '123456789',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categoryId = DB::table('categories')->first()->id;
    $genderId = DB::table('genders')->first()->id;
    $paymentMethodId = DB::table('payment_methods')->first()->id;

    $product = Product::create([
        'store_id' => $storeId,
        'category_id' => $categoryId,
        'gender_id' => $genderId,
        'payment_method_id' => $paymentMethodId,
        'name' => 'Premium Jacket',
        'description' => 'A very fine jacket.',
        'original_price' => 500.00,
        'status' => Product::STATUS_PUBLISHED,
    ]);

    $response = $this->get(route('admin.products.stats', $product));
    $response->assertRedirect(route('login'));
});

test('authenticated users can access product performance stats json payload', function () {
    $this->seed(CatalogSeeder::class);

    $user = User::factory()->create();
    $this->actingAs($user);

    $storeId = DB::table('stores')->insertGetId([
        'user_id' => $user->id,
        'store_name' => 'Test Store',
        'phone_number' => '123456789',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categoryId = DB::table('categories')->first()->id;
    $genderId = DB::table('genders')->first()->id;
    $paymentMethodId = DB::table('payment_methods')->first()->id;

    $product = Product::create([
        'store_id' => $storeId,
        'category_id' => $categoryId,
        'gender_id' => $genderId,
        'payment_method_id' => $paymentMethodId,
        'name' => 'Premium Jacket',
        'description' => 'A very fine jacket.',
        'original_price' => 500.00,
        'status' => Product::STATUS_PUBLISHED,
    ]);

    // Create likes manually
    for ($i = 0; $i < 3; $i++) {
        $liker = User::factory()->create();
        DB::table('liked_products')->insert([
            'user_id' => $liker->id,
            'product_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Create saves manually
    for ($i = 0; $i < 2; $i++) {
        $saver = User::factory()->create();
        DB::table('saved_products')->insert([
            'user_id' => $saver->id,
            'product_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $response = $this->getJson(route('admin.products.stats', $product));

    $response->assertOk()
        ->assertJsonStructure([
            'kpis' => [
                'liked_count',
                'saved_count',
                'orders_count',
                'drops_count',
            ],
            'liked_users' => [
                'data',
                'current_page',
                'last_page',
            ],
            'saved_users' => [
                'data',
                'current_page',
                'last_page',
            ],
            'drops' => [
                'data',
                'current_page',
                'last_page',
            ],
        ])
        ->assertJsonPath('kpis.liked_count', 3)
        ->assertJsonPath('kpis.saved_count', 2);
});
