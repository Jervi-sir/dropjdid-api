<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $wilayaIds = DB::table('wilayas')->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            // Around 35% of users own stores
            if (! fake()->boolean(35)) {
                continue;
            }

            $storesCount = fake()->numberBetween(1, 30);

            for ($i = 1; $i <= $storesCount; $i++) {
                DB::table('stores')->insert([
                    'user_id' => $userId,
                    'wilaya_id' => fake()->randomElement($wilayaIds),

                    'store_name' => fake()->company().' '.$i,
                    'phone_number' => '05'.fake()->unique()->numerify('########'),
                    'password' => Hash::make('password'),
                    'logo' => fake()->boolean(60) ? ('https://fpoimg.com/600x400?text='.'business'.$i) : null,
                    'description' => fake()->boolean(80) ? fake()->paragraph() : null,

                    'balance' => fake()->randomFloat(2, 0, 500000),

                    'status' => fake()->randomElement([
                        Store::STATUS_PENDING,
                        Store::STATUS_ACTIVE,
                        Store::STATUS_SUSPENED,
                    ]),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
