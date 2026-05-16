<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $storeIds = DB::table('stores')->pluck('id')->toArray();
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        $genderIds = DB::table('genders')->pluck('id')->toArray();
        $qualityIds = DB::table('qualities')->pluck('id')->toArray();
        $paymentMethodIds = DB::table('payment_methods')->pluck('id')->toArray();
        $userIds = DB::table('users')->pluck('id')->toArray();

        $keywords = DB::table('keywords')
            ->join('labels', 'labels.id', '=', 'keywords.label_id')
            ->select('keywords.id as keyword_id', 'labels.id as label_id')
            ->get();

        for ($i = 1; $i <= 300; $i++) {
            $categoryId = fake()->randomElement($categoryIds);

            $originalPrice = fake()->randomFloat(2, 1500, 50000);
            $storePrice = fake()->randomFloat(2, 1000, $originalPrice);
            $showPrice = fake()->randomFloat(2, $storePrice, $originalPrice + 10000);

            $productId = DB::table('products')->insertGetId([
                'store_id' => fake()->randomElement($storeIds),
                'category_id' => $categoryId,
                'gender_id' => fake()->randomElement($genderIds),
                'quality_id' => fake()->randomElement($qualityIds),
                'payment_method_id' => fake()->randomElement($paymentMethodIds),

                'name' => fake()->words(3, true),
                'description' => fake()->paragraph(),

                'original_price' => $originalPrice,
                'show_price' => $showPrice,
                'store_price' => $storePrice,

                'status' => fake()->randomElement([
                    Product::STATUS_DRAFT,
                    Product::STATUS_PUBLISHED,
                    Product::STATUS_ARCHIVED,
                    Product::STATUS_REJECTED,
                ]),
                'rejection_reason' => fake()->boolean(10) ? fake()->sentence() : null,
                'refreshed_at' => fake()->boolean(60) ? now()->subDays(fake()->numberBetween(0, 30)) : null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Images: 1 to 5 per product
            $imagesCount = fake()->numberBetween(1, 5);

            for ($j = 1; $j <= $imagesCount; $j++) {
                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image' => 'https://fpoimg.com/400x800?text='.'fashion'.$i.'_'.$j,
                    'sort_order' => $j,
                    'is_main' => $j === 1,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Keywords: 2 to 8 per product
            foreach ($keywords->random(fake()->numberBetween(2, 8)) as $keyword) {
                DB::table('product_keywords')->insert([
                    'product_id' => $productId,
                    'keyword_id' => $keyword->keyword_id,
                    'label_id' => $keyword->label_id,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Variants: sizes based on category
            $sizeIds = DB::table('sizes')
                ->where('category_id', $categoryId)
                ->pluck('id')
                ->toArray();

            foreach (fake()->randomElements($sizeIds, fake()->numberBetween(1, min(5, count($sizeIds)))) as $sizeId) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'size_id' => $sizeId,
                    'quantity' => fake()->numberBetween(0, 100),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Likes: 0 to 30 users per product
            foreach (fake()->randomElements($userIds, fake()->numberBetween(0, min(30, count($userIds)))) as $userId) {
                DB::table('liked_products')->insert([
                    'user_id' => $userId,
                    'product_id' => $productId,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
