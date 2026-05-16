<?php

namespace Database\Seeders;

use App\Models\SearchHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SearchSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        $queries = [
            'nike shoes',
            'adidas',
            'sacoche',
            'tommy hilfiger',
            'montre homme',
            'mules femme',
            'sneakers',
            'robe',
            'parfum',
            'gym',
            'casquette',
            'pull',
            'veste',
            'sac femme',
            'chaussures homme',
            'accessoires',
            'premium copy',
            'original',
        ];

        foreach ($userIds as $userId) {
            if (! fake()->boolean(60)) {
                continue;
            }

            $count = fake()->numberBetween(1, 60);

            for ($i = 1; $i <= $count; $i++) {
                DB::table('search_histories')->insert([
                    'user_id' => $userId,
                    'query' => fake()->randomElement($queries),
                    'type' => fake()->randomElement([
                        SearchHistory::TYPE_GENERAL,
                        SearchHistory::TYPE_PRODUCT,
                        SearchHistory::TYPE_STORE,
                        SearchHistory::TYPE_CREATOR,
                        null,
                    ]),
                    'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
