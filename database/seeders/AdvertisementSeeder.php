<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $status = fake()->randomElement([
                'draft',
                'active',
                'active',
                'inactive',
            ]);

            DB::table('advertisements')->insert([
                'title' => fake()->boolean(80) ? fake()->sentence(3) : null,
                'image' => 'https://fpoimg.com/1200x500?text='.'ads'.$i,
                'url' => fake()->boolean(70) ? fake()->url() : null,
                'description' => fake()->boolean(70) ? fake()->sentence() : null,
                'status' => $status,
                'sort_order' => $i,

                'starts_at' => $status === 'active'
                    ? now()->subDays(fake()->numberBetween(1, 10))
                    : fake()->optional()->dateTimeBetween('-30 days', '+10 days'),

                'ends_at' => $status === 'active'
                    ? now()->addDays(fake()->numberBetween(5, 30))
                    : fake()->optional()->dateTimeBetween('+1 days', '+60 days'),

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
