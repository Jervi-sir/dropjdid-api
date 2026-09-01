<?php

namespace Database\Seeders;

use App\Models\Drop;
use App\Models\DropImage;
use App\Models\DropProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curatedDropThemes = [
            [
                'title' => 'Summer Streetwear Essentials 2026',
                'description' => 'A curated selection of the cleanest summer fits, baggy cargos, and retro kicks for the heat.',
            ],
            [
                'title' => 'Vintage Retro Panda & Heavyweight Hoodies',
                'description' => 'Exclusive black and white colorway collection featuring heavyweight comfort fleece.',
            ],
            [
                'title' => 'Algiers Sneaker Vault Collection',
                'description' => 'Limited edition drops from top tier designers, verified originals ready to ship across 58 wilayas.',
            ],
            [
                'title' => 'Midnight Streetwear Edition',
                'description' => 'Dark aesthetic apparel featuring oversized box logos, graphic tees, and chunky trainers.',
            ],
            [
                'title' => 'Casual Everyday Drip DZ',
                'description' => 'Relaxed fits for coffee runs, university, and everyday styling in Algeria.',
            ],
            [
                'title' => 'London Drill & Trapstar Capsule',
                'description' => 'Iconic UK streetwear styles, reflective tracksuits, and hyperdrive down puffers.',
            ],
            [
                'title' => 'Corteiz Guerillaz Secret Drop',
                'description' => 'Strictly limited edition Alcatraz items, heavyweight cotton tees, and iconic tactical cargos.',
            ],
            [
                'title' => 'Retro Basketball Legends Archive',
                'description' => 'High-heat Air Jordan and Nike Dunk silhouettes from the golden era of basketball culture.',
            ],
            [
                'title' => 'Y2K Cyberpunk & Baggy Denim Wave',
                'description' => 'Oversized skate denim, chunky shoes, and nostalgic early 2000s street graphics.',
            ],
            [
                'title' => 'Tokyo Harajuku Minimalist Influx',
                'description' => 'Clean Japanese streetwear aesthetics, relaxed boxy silhouettes, and earthy muted tones.',
            ],
            [
                'title' => 'Gorpcore Trail & Outdoor Technicals',
                'description' => 'Weatherproof gear, Salomon runners, Arc\'teryx jackets, and functional utility wear.',
            ],
            [
                'title' => 'Casablanca Luxury Resort & Tennis Club',
                'description' => 'Elevated streetwear with silk-touch graphics, vibrant vacation prints, and luxury details.',
            ],
            [
                'title' => 'Autumn Earth Tones & Cozy Fleeces',
                'description' => 'Warm mocha, desert sand, and olive green fleece hoodies with matching track bottoms.',
            ],
            [
                'title' => 'Terrace Culture Samba & Palermo Select',
                'description' => 'Classic low-profile gum sole kicks paired with retro quarter-zip pullovers and track tops.',
            ],
            [
                'title' => 'Underground Skate & Heavyweight Tees',
                'description' => 'Durable 280 GSM cotton tees, wide-leg utility trousers, and skate-ready footwear.',
            ],
            [
                'title' => 'Monochrome Minimalist Archive',
                'description' => 'Timeless black and white wardrobe essentials engineered with premium fabrics.',
            ],
            [
                'title' => 'Hydra High-Heat Designer Drop',
                'description' => 'The rarest streetwear pieces, luxury designer collaborations, and authentic grails.',
            ],
            [
                'title' => 'Oran Seaside Sunset Drip',
                'description' => 'Bright summer tees, lightweight parachute pants, and breathable mesh sneakers.',
            ],
            [
                'title' => 'Constantine Heritage & Retro Street',
                'description' => 'Authentic vintage varsity jackets, classic sneakers, and statement headwear.',
            ],
            [
                'title' => 'Acid Wash & Distressed Vintage Capsule',
                'description' => 'Sun-faded washes, vintage distressing, hand-finished tees, and oversized hoodies.',
            ],
        ];

        // Generators for the remaining 80 drops to reach 100
        $dropPrefixes = [
            'Exclusive', 'Limited Edition', 'Official', 'Curated', 'Signature',
            'Vault', 'Special Release', 'Weekend Flash', 'Members Only', 'Archive'
        ];

        $dropStyles = [
            'Streetwear Capsule', 'Sneaker Heat Wave', 'Techwear Essentials', 'Varsity Collection',
            'Oversized Boxy Fit Drop', 'Heavyweight Fleece Selection', 'All-Black Tactical Series',
            'Retro 90s Revival', 'Drill & Tracksuit Pack', 'Vintage Denim & Workwear',
            'Graphic Tee Influx', 'Clean Minimalist Uniform', 'Summer Drip Vault', 'Winter Down Puffer Edit'
        ];

        $dropLocations = [
            'Algiers', 'Oran', 'Constantine', 'Annaba', 'Setif', 'Blida', 'Tlemcen', 'Batna', 'Béjaïa', 'Mostaganem'
        ];

        $dropImages = [
            'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?w=800',
            'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800',
            'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=800',
            'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800',
            'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800',
            'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800',
            'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=800',
            'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=800',
            'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800',
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=800',
            'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800',
            'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800',
            'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=800',
            'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800',
            'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=600',
            'https://images.unsplash.com/photo-1544441893-675973e31985?w=600',
        ];

        $allDrops = $curatedDropThemes;
        $dropIndex = 1;
        $totalTarget = 100;

        while (count($allDrops) < $totalTarget) {
            $prefix = $dropPrefixes[$dropIndex % count($dropPrefixes)];
            $style = $dropStyles[($dropIndex * 3) % count($dropStyles)];
            $location = $dropLocations[($dropIndex * 7) % count($dropLocations)];
            $year = '2026';

            $title = "{$prefix} {$style} - {$location} Vol. " . (($dropIndex % 9) + 1);
            $description = "Curated street collection showcasing the finest {$style} available in {$location}. Verified original pieces and premium styling for the season {$year}.";

            $allDrops[] = [
                'title' => $title,
                'description' => $description,
            ];

            $dropIndex++;
        }

        $users = User::all();
        $allProducts = Product::all();

        foreach ($allDrops as $idx => $data) {
            $creator = $users->isNotEmpty() ? $users->random() : null;

            $drop = Drop::firstOrCreate(
                ['title' => $data['title']],
                [
                    'creator_id' => $creator?->id,
                    'description' => $data['description'],
                    'drop_status' => 'published',
                ]
            );

            // 1. Drop Images (1 to 3 images)
            $numImages = ($idx % 3) + 1;
            for ($i = 0; $i < $numImages; $i++) {
                $imgUrl = $dropImages[($idx + $i * 4) % count($dropImages)];

                DropImage::firstOrCreate(
                    [
                        'drop_id' => $drop->id,
                        'image' => $imgUrl,
                    ],
                    [
                        'drop_id' => $drop->id,
                        'image' => $imgUrl,
                        'sort_order' => $i,
                        'is_main' => $i === 0,
                    ]
                );
            }

            // 2. Attach Products (3 to 8 products per drop)
            if ($allProducts->isNotEmpty()) {
                $countToPick = min(rand(3, 8), $allProducts->count());
                $selectedProducts = $allProducts->random($countToPick);

                foreach ($selectedProducts as $product) {
                    DropProduct::firstOrCreate([
                        'drop_id' => $drop->id,
                        'product_id' => $product->id,
                    ], [
                        'drop_price' => $product->price_shown ?? 15000.00,
                    ]);
                }
            }
        }
    }
}
