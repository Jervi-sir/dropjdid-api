<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        $labelRows = [];

        for ($i = 1; $i <= 100; $i++) {
            $code = 'label_'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $labelRows[] = [
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
