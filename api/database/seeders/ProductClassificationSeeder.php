<?php

namespace Database\Seeders;

use App\Models\ProductClassification;
use Illuminate\Database\Seeder;

class ProductClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            [
                'code' => 'first_choice',
                'en' => 'First choice of many people.',
                'fr' => 'Premier choix de nombreuses personnes.',
                'ar' => 'الخيار الأول للكثيرين.',
            ],
            [
                'code' => 'quantity_limited',
                'en' => 'Quantity limited.',
                'fr' => 'Quantité limitée.',
                'ar' => 'كمية محدودة.',
            ],
            [
                'code' => 'customer_favorite',
                'en' => 'Customer favorite.',
                'fr' => 'Favori des clients.',
                'ar' => 'المفضل لدى الزبائن.',
            ],
            [
                'code' => 'selling_fast',
                'en' => 'Selling fast.',
                'fr' => 'Vente rapide.',
                'ar' => 'يباع بسرعة.',
            ],
            [
                'code' => 'best_value',
                'en' => 'Best value.',
                'fr' => 'Meilleur rapport qualité-prix.',
                'ar' => 'أفضل قيمة مقابل السعر.',
            ],
            [
                'code' => 'new_arrival',
                'en' => 'New arrival.',
                'fr' => 'Nouvelle arrivée.',
                'ar' => 'وصل حديثاً.',
            ],
            [
                'code' => 'highly_rated',
                'en' => 'Highly rated.',
                'fr' => 'Très bien noté.',
                'ar' => 'تقييم عالي.',
            ],
        ];

        foreach ($classifications as $classification) {
            ProductClassification::updateOrCreate(
                ['code' => $classification['code']],
                $classification
            );
        }
    }
}
