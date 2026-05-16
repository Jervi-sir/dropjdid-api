<?php

namespace Database\Seeders;

use App\Models\Drop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DropSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();

        for ($i = 1; $i <= 220; $i++) {
            $dropId = DB::table('drops')->insertGetId([
                'creator_id' => fake()->randomElement($userIds),
                'title' => fake()->sentence(3),
                'description' => fake()->boolean(80) ? fake()->paragraph() : null,
                'status' => $status = fake()->randomElement([
                    Drop::STATUS_DRAFT,
                    Drop::STATUS_PUBLISHED,
                    Drop::STATUS_ENDED,
                    Drop::STATUS_CANCELLED,
                    Drop::STATUS_REJECTED,
                ]),
                'rejection_reason' => $status === Drop::STATUS_REJECTED ? json_encode([
                    [
                        'id' => fake()->numberBetween(1, 10),
                        'en' => fake()->sentence(),
                        'fr' => fake()->sentence(),
                        'ar' => fake()->sentence(),
                    ]
                ]) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Images: 1 to 5
            $imagesCount = fake()->numberBetween(1, 5);

            for ($j = 1; $j <= $imagesCount; $j++) {
                DB::table('drop_images')->insert([
                    'drop_id' => $dropId,
                    'image' => 'https://fpoimg.com/400x800?text='.'fashion'.$i.'_'.$j,
                    'sort_order' => $j,
                    'is_main' => $j === 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Products: 5 to 40 per drop
            $selectedProductIds = fake()->randomElements(
                $productIds,
                min(fake()->numberBetween(5, 40), count($productIds))
            );

            foreach ($selectedProductIds as $productId) {
                $showPrice = DB::table('products')
                    ->where('id', $productId)
                    ->value('show_price');

                DB::table('drop_product')->insert([
                    'drop_id' => $dropId,
                    'product_id' => $productId,
                    'drop_price' => fake()->boolean(70)
                        ? fake()->randomFloat(2, 500, $showPrice ?: 50000)
                        : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Likes: 0 to 60 users
            foreach (fake()->randomElements($userIds, fake()->numberBetween(0, min(60, count($userIds)))) as $userId) {
                DB::table('liked_drops')->insert([
                    'user_id' => $userId,
                    'drop_id' => $dropId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
