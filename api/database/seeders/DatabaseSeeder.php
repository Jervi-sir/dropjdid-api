<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            CommuneSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            StoreSeeder::class,
            DeliveryCostSeeder::class,
            WalletSeeder::class,
            LabelSeeder::class,
            ProductSeeder::class,
            DropSeeder::class,
            OrderSeeder::class,
            PrizeSeeder::class,
            AdvertisementSeeder::class,
            EventSeeder::class,
            SaveLikeSeeder::class,
        ]);
    }
}
