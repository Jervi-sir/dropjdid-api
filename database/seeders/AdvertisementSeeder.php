<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ads = [
            [
                'id' => 1,
                'title' => 'DropJdid Summer Drop 2026',
                'image' => 'https://picsum.photos/seed/ad1/800/1200',
                'url' => 'https://dropjdid.com',
                'description' => 'sponsored',
                'status' => 'active',
                'sort_order' => 1,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(30),
            ],
            [
                'id' => 2,
                'title' => 'Limited Sneakers Edition',
                'image' => 'https://picsum.photos/seed/ad2/800/1200',
                'url' => 'https://dropjdid.com',
                'description' => 'sponsored',
                'status' => 'active',
                'sort_order' => 2,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
            ],
            [
                'id' => 3,
                'title' => 'Streetwear Oversized Hoodies',
                'image' => 'https://picsum.photos/seed/ad3/800/1200',
                'url' => 'https://dropjdid.com',
                'description' => 'sponsored',
                'status' => 'active',
                'sort_order' => 3,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(20),
            ],
            [
                'id' => 4,
                'title' => 'Vintage Accessories Collection',
                'image' => 'https://picsum.photos/seed/ad4/800/1200',
                'url' => 'https://dropjdid.com',
                'description' => 'sponsored',
                'status' => 'active',
                'sort_order' => 4,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(15),
            ],
        ];

        foreach ($ads as $adData) {
            Advertisement::updateOrCreate(
                ['id' => $adData['id']],
                $adData
            );
        }
    }
}
