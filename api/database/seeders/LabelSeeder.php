<?php

namespace Database\Seeders;

use App\Models\Keyword;
use App\Models\Label;
use App\Models\LabelCategory;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category' => [
                    'code' => 'brands',
                    'en' => 'Brands',
                    'fr' => 'Marques',
                    'ar' => 'ماركات',
                    'icon' => 'tag',
                ],
                'labels' => [
                    [
                        'code' => 'nike',
                        'en' => 'Nike',
                        'fr' => 'Nike',
                        'ar' => 'نايكي',
                        'keywords' => ['nike', 'swoosh', 'dunk', 'airforce', 'jordan'],
                    ],
                    [
                        'code' => 'adidas',
                        'en' => 'Adidas',
                        'fr' => 'Adidas',
                        'ar' => 'أديداس',
                        'keywords' => ['adidas', 'samba', 'campus', 'originals', 'yeezy'],
                    ],
                    [
                        'code' => 'zara',
                        'en' => 'Zara',
                        'fr' => 'Zara',
                        'ar' => 'زارا',
                        'keywords' => ['zara', 'man', 'fastfashion', 'chic'],
                    ],
                    [
                        'code' => 'essentials',
                        'en' => 'Essentials',
                        'fr' => 'Essentials',
                        'ar' => 'إيسنشالز',
                        'keywords' => ['essentials', 'fog', 'fearofgod', 'hoodie'],
                    ],
                    [
                        'code' => 'supreme',
                        'en' => 'Supreme',
                        'fr' => 'Supreme',
                        'ar' => 'سوبريم',
                        'keywords' => ['supreme', 'boxlogo', 'streetwear', 'nyc'],
                    ],
                ],
            ],
            [
                'category' => [
                    'code' => 'styles',
                    'en' => 'Styles',
                    'fr' => 'Styles',
                    'ar' => 'أنماط',
                    'icon' => 'style',
                ],
                'labels' => [
                    [
                        'code' => 'streetwear',
                        'en' => 'Streetwear',
                        'fr' => 'Streetwear',
                        'ar' => 'ستريت وير',
                        'keywords' => ['streetwear', 'drip', 'urban', 'hypebeast', 'street'],
                    ],
                    [
                        'code' => 'vintage',
                        'en' => 'Vintage',
                        'fr' => 'Vintage / Rétro',
                        'ar' => 'فينتاج',
                        'keywords' => ['vintage', 'retro', '90s', 'thrift', 'oldmoney'],
                    ],
                    [
                        'code' => 'casual',
                        'en' => 'Casual',
                        'fr' => 'Décontracté',
                        'ar' => 'يومي كاجوال',
                        'keywords' => ['casual', 'simple', 'everyday', 'basic'],
                    ],
                    [
                        'code' => 'sporty',
                        'en' => 'Sporty',
                        'fr' => 'Sportif',
                        'ar' => 'رياضي',
                        'keywords' => ['sport', 'gym', 'training', 'activewear'],
                    ],
                ],
            ],
            [
                'category' => [
                    'code' => 'fits',
                    'en' => 'Fits & Cuts',
                    'fr' => 'Coupes',
                    'ar' => 'القصات',
                    'icon' => 'scissors',
                ],
                'labels' => [
                    [
                        'code' => 'oversized',
                        'en' => 'Oversized',
                        'fr' => 'Oversize',
                        'ar' => 'أوفر سايز',
                        'keywords' => ['oversize', 'baggy', 'loose', 'wide'],
                    ],
                    [
                        'code' => 'slim_fit',
                        'en' => 'Slim Fit',
                        'fr' => 'Coupe ajustée',
                        'ar' => 'سليم فيت',
                        'keywords' => ['slim', 'fitted', 'tight'],
                    ],
                    [
                        'code' => 'regular_fit',
                        'en' => 'Regular Fit',
                        'fr' => 'Coupe standard',
                        'ar' => 'قصة عادية',
                        'keywords' => ['regular', 'standard', 'classic'],
                    ],
                ],
            ],
        ];

        foreach ($categories as $group) {
            $cat = LabelCategory::firstOrCreate(
                ['code' => $group['category']['code']],
                $group['category']
            );

            foreach ($group['labels'] as $lbl) {
                $label = Label::firstOrCreate(
                    [
                        'label_category_id' => $cat->id,
                        'code' => $lbl['code'],
                    ],
                    [
                        'label_category_id' => $cat->id,
                        'code' => $lbl['code'],
                        'en' => $lbl['en'],
                        'fr' => $lbl['fr'],
                        'ar' => $lbl['ar'],
                    ]
                );

                foreach ($lbl['keywords'] as $kwCode) {
                    Keyword::firstOrCreate([
                        'label_id' => $label->id,
                        'code' => $kwCode,
                    ]);
                }
            }
        }
    }
}
