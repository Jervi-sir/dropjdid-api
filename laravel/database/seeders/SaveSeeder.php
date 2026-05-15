<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaveSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();
        $dropIds = DB::table('drops')->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            foreach (fake()->randomElements($productIds, fake()->numberBetween(0, min(40, count($productIds)))) as $productId) {
                DB::table('saved_products')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'product_id' => $productId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            foreach (fake()->randomElements($dropIds, fake()->numberBetween(0, min(25, count($dropIds)))) as $dropId) {
                DB::table('saved_drops')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'drop_id' => $dropId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
