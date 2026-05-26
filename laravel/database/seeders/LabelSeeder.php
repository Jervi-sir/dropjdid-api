<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        $categoryRows = [
            [
                'code' => 'brands',
                'en' => 'Brands',
                'fr' => 'Marques',
                'ar' => 'العلامات التجارية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'styles',
                'en' => 'Styles',
                'fr' => 'Styles',
                'ar' => 'الأنماط',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'occasions',
                'en' => 'Occasions',
                'fr' => 'Occasions',
                'ar' => 'المناسبات',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'materials',
                'en' => 'Materials',
                'fr' => 'Matériaux',
                'ar' => 'المواد',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'seasons',
                'en' => 'Seasons',
                'fr' => 'Saisons',
                'ar' => 'الفصول',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('label_categories')->insert($categoryRows);

        $categories = DB::table('label_categories')->pluck('id')->toArray();

        $labelRows = [];

        for ($i = 1; $i <= 100; $i++) {
            $code = 'label_'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $labelRows[] = [
                'label_category_id' => $categories[$i % count($categories)],
                'code' => $code,
                'en' => 'Label '.$i,
                'fr' => 'Libellé '.$i,
                'ar' => 'وسم '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('labels')->insert($labelRows);

        $labels = DB::table('labels')
            ->latest('id')
            ->limit(100)
            ->pluck('id', 'code');

        $keywordRows = [];

        foreach ($labels as $labelCode => $labelId) {
            for ($i = 1; $i <= 6; $i++) {
                $keywordRows[] = [
                    'label_id' => $labelId,
                    'code' => $labelCode.'_keyword_'.$i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('keywords')->insert($keywordRows);
    }
}
