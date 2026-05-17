<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(LookupTableSeeder::class);
        // $this->call(DemoDataSeeder::class);

        foreach ([
            // CatalogSeeder::class,
            // UserSeeder::class,
            // StoreSeeder::class,
            // LabelSeeder::class,
            ProductSeeder::class,
            CreatorSeeder::class,
            DropSeeder::class,
            SaveSeeder::class,
            PrizeSeeder::class,
            OrderSeeder::class,
            AdvertisementSeeder::class,
            ConversationSeeder::class,
            SearchSeeder::class,
            WalletSeeder::class,
            NotificationSeeder::class,
            SupportRequestSeeder::class,
        ] as $seeder) {
            DB::transaction(fn () => $this->call($seeder));
        }

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
