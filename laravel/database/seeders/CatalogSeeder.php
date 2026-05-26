<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedWilayas();
        $this->seedPaymentMethods();
        $this->seedGenders();
        $this->seedSocialPlatforms();
        $this->seedCategories();
        $this->seedSizes();
        $this->seedQualities();
    }

    private function seedRoles(): void
    {
        $rows = [
            ['code' => 'admin', 'en' => 'Admin', 'fr' => 'Admin', 'ar' => 'مدير'],
            ['code' => 'client', 'en' => 'Client', 'fr' => 'Client', 'ar' => 'زبون'],
            ['code' => 'creator', 'en' => 'Creator', 'fr' => 'Créateur', 'ar' => 'صانع محتوى'],
            ['code' => 'sgm', 'en' => 'sgm', 'fr' => 'sgm', 'ar' => 'sgm'],
        ];

        $this->upsert('roles', $rows);
    }

    private function seedWilayas(): void
    {
        $wilayas = [
            ['number' => '01', 'code' => 'adrar', 'en' => 'Adrar', 'fr' => 'Adrar', 'ar' => 'أدرار'],
            ['number' => '02', 'code' => 'chlef', 'en' => 'Chlef', 'fr' => 'Chlef', 'ar' => 'الشلف'],
            ['number' => '03', 'code' => 'laghouat', 'en' => 'Laghouat', 'fr' => 'Laghouat', 'ar' => 'الأغواط'],
            ['number' => '04', 'code' => 'oum_el_bouaghi', 'en' => 'Oum El Bouaghi', 'fr' => 'Oum El Bouaghi', 'ar' => 'أم البواقي'],
            ['number' => '05', 'code' => 'batna', 'en' => 'Batna', 'fr' => 'Batna', 'ar' => 'باتنة'],
            ['number' => '06', 'code' => 'bejaia', 'en' => 'Bejaia', 'fr' => 'Béjaïa', 'ar' => 'بجاية'],
            ['number' => '07', 'code' => 'biskra', 'en' => 'Biskra', 'fr' => 'Biskra', 'ar' => 'بسكرة'],
            ['number' => '08', 'code' => 'bechar', 'en' => 'Bechar', 'fr' => 'Béchar', 'ar' => 'بشار'],
            ['number' => '09', 'code' => 'blida', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة'],
            ['number' => '10', 'code' => 'bouira', 'en' => 'Bouira', 'fr' => 'Bouira', 'ar' => 'البويرة'],
            ['number' => '11', 'code' => 'tamanrasset', 'en' => 'Tamanrasset', 'fr' => 'Tamanrasset', 'ar' => 'تمنراست'],
            ['number' => '12', 'code' => 'tebessa', 'en' => 'Tebessa', 'fr' => 'Tébessa', 'ar' => 'تبسة'],
            ['number' => '13', 'code' => 'tlemcen', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
            ['number' => '14', 'code' => 'tiaret', 'en' => 'Tiaret', 'fr' => 'Tiaret', 'ar' => 'تيارت'],
            ['number' => '15', 'code' => 'tizi_ouzou', 'en' => 'Tizi Ouzou', 'fr' => 'Tizi Ouzou', 'ar' => 'تيزي وزو'],
            ['number' => '16', 'code' => 'alger', 'en' => 'Algiers', 'fr' => 'Alger', 'ar' => 'الجزائر'],
            ['number' => '17', 'code' => 'djelfa', 'en' => 'Djelfa', 'fr' => 'Djelfa', 'ar' => 'الجلفة'],
            ['number' => '18', 'code' => 'jijel', 'en' => 'Jijel', 'fr' => 'Jijel', 'ar' => 'جيجل'],
            ['number' => '19', 'code' => 'setif', 'en' => 'Setif', 'fr' => 'Sétif', 'ar' => 'سطيف'],
            ['number' => '20', 'code' => 'saida', 'en' => 'Saida', 'fr' => 'Saïda', 'ar' => 'سعيدة'],
            ['number' => '21', 'code' => 'skikda', 'en' => 'Skikda', 'fr' => 'Skikda', 'ar' => 'سكيكدة'],
            ['number' => '22', 'code' => 'sidi_bel_abbes', 'en' => 'Sidi Bel Abbes', 'fr' => 'Sidi Bel Abbès', 'ar' => 'سيدي بلعباس'],
            ['number' => '23', 'code' => 'annaba', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
            ['number' => '24', 'code' => 'guelma', 'en' => 'Guelma', 'fr' => 'Guelma', 'ar' => 'قالمة'],
            ['number' => '25', 'code' => 'constantine', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
            ['number' => '26', 'code' => 'medea', 'en' => 'Medea', 'fr' => 'Médéa', 'ar' => 'المدية'],
            ['number' => '27', 'code' => 'mostaganem', 'en' => 'Mostaganem', 'fr' => 'Mostaganem', 'ar' => 'مستغانم'],
            ['number' => '28', 'code' => 'msila', 'en' => "M'Sila", 'fr' => "M'Sila", 'ar' => 'المسيلة'],
            ['number' => '29', 'code' => 'mascara', 'en' => 'Mascara', 'fr' => 'Mascara', 'ar' => 'معسكر'],
            ['number' => '30', 'code' => 'ouargla', 'en' => 'Ouargla', 'fr' => 'Ouargla', 'ar' => 'ورقلة'],
            ['number' => '31', 'code' => 'oran', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران'],
            ['number' => '32', 'code' => 'el_bayadh', 'en' => 'El Bayadh', 'fr' => 'El Bayadh', 'ar' => 'البيض'],
            ['number' => '33', 'code' => 'illizi', 'en' => 'Illizi', 'fr' => 'Illizi', 'ar' => 'إليزي'],
            ['number' => '34', 'code' => 'bordj_bou_arreridj', 'en' => 'Bordj Bou Arreridj', 'fr' => 'Bordj Bou Arréridj', 'ar' => 'برج بوعريريج'],
            ['number' => '35', 'code' => 'boumerdes', 'en' => 'Boumerdes', 'fr' => 'Boumerdès', 'ar' => 'بومرداس'],
            ['number' => '36', 'code' => 'el_tarf', 'en' => 'El Tarf', 'fr' => 'El Tarf', 'ar' => 'الطارف'],
            ['number' => '37', 'code' => 'tindouf', 'en' => 'Tindouf', 'fr' => 'Tindouf', 'ar' => 'تندوف'],
            ['number' => '38', 'code' => 'tissemsilt', 'en' => 'Tissemsilt', 'fr' => 'Tissemsilt', 'ar' => 'تيسمسيلت'],
            ['number' => '39', 'code' => 'el_oued', 'en' => 'El Oued', 'fr' => 'El Oued', 'ar' => 'الوادي'],
            ['number' => '40', 'code' => 'khenchela', 'en' => 'Khenchela', 'fr' => 'Khenchela', 'ar' => 'خنشلة'],
            ['number' => '41', 'code' => 'souk_ahras', 'en' => 'Souk Ahras', 'fr' => 'Souk Ahras', 'ar' => 'سوق أهراس'],
            ['number' => '42', 'code' => 'tipaza', 'en' => 'Tipaza', 'fr' => 'Tipaza', 'ar' => 'تيبازة'],
            ['number' => '43', 'code' => 'mila', 'en' => 'Mila', 'fr' => 'Mila', 'ar' => 'ميلة'],
            ['number' => '44', 'code' => 'ain_defla', 'en' => 'Ain Defla', 'fr' => 'Aïn Defla', 'ar' => 'عين الدفلى'],
            ['number' => '45', 'code' => 'naama', 'en' => 'Naama', 'fr' => 'Naâma', 'ar' => 'النعامة'],
            ['number' => '46', 'code' => 'ain_temouchent', 'en' => 'Ain Temouchent', 'fr' => 'Aïn Témouchent', 'ar' => 'عين تموشنت'],
            ['number' => '47', 'code' => 'ghardaia', 'en' => 'Ghardaia', 'fr' => 'Ghardaïa', 'ar' => 'غرداية'],
            ['number' => '48', 'code' => 'relizane', 'en' => 'Relizane', 'fr' => 'Relizane', 'ar' => 'غليزان'],
            ['number' => '49', 'code' => 'timimoun', 'en' => 'Timimoun', 'fr' => 'Timimoun', 'ar' => 'تيميمون'],
            ['number' => '50', 'code' => 'bordj_badji_mokhtar', 'en' => 'Bordj Badji Mokhtar', 'fr' => 'Bordj Badji Mokhtar', 'ar' => 'برج باجي مختار'],
            ['number' => '51', 'code' => 'ouled_djellal', 'en' => 'Ouled Djellal', 'fr' => 'Ouled Djellal', 'ar' => 'أولاد جلال'],
            ['number' => '52', 'code' => 'beni_abbes', 'en' => 'Beni Abbes', 'fr' => 'Béni Abbès', 'ar' => 'بني عباس'],
            ['number' => '53', 'code' => 'in_salah', 'en' => 'In Salah', 'fr' => 'In Salah', 'ar' => 'عين صالح'],
            ['number' => '54', 'code' => 'in_guezzam', 'en' => 'In Guezzam', 'fr' => 'In Guezzam', 'ar' => 'عين قزام'],
            ['number' => '55', 'code' => 'touggourt', 'en' => 'Touggourt', 'fr' => 'Touggourt', 'ar' => 'تقرت'],
            ['number' => '56', 'code' => 'djanet', 'en' => 'Djanet', 'fr' => 'Djanet', 'ar' => 'جانت'],
            ['number' => '57', 'code' => 'el_mghair', 'en' => "El M'Ghair", 'fr' => "El M'Ghair", 'ar' => 'المغير'],
            ['number' => '58', 'code' => 'el_meniaa', 'en' => 'El Meniaa', 'fr' => 'El Meniaa', 'ar' => 'المنيعة'],
        ];

        // Algeria has 58 official wilayas.
        // If your app needs 69 entries, add the 11 custom/new ones here.
        foreach (range(59, 69) as $number) {
            $wilayas[] = [
                'number' => (string) $number,
                'code' => 'wilaya_'.$number,
                'en' => 'Wilaya '.$number,
                'fr' => 'Wilaya '.$number,
                'ar' => 'ولاية '.$number,
            ];
        }

        $this->upsert('wilayas', $wilayas);
    }

    private function seedPaymentMethods(): void
    {
        $rows = [
            ['code' => 'cash_on_delivery', 'en' => 'Cash on delivery', 'fr' => 'Paiement à la livraison', 'ar' => 'الدفع عند الاستلام'],
            ['code' => 'card', 'en' => 'Card', 'fr' => 'Carte bancaire', 'ar' => 'بطاقة بنكية'],
            ['code' => 'baridimob', 'en' => 'BaridiMob', 'fr' => 'BaridiMob', 'ar' => 'بريدي موب'],
            ['code' => 'ccp', 'en' => 'CCP', 'fr' => 'CCP', 'ar' => 'الحساب البريدي الجاري'],
        ];

        $this->upsert('payment_methods', $rows);
    }

    private function seedGenders(): void
    {
        $rows = [
            ['code' => 'male', 'en' => 'Male', 'fr' => 'Homme', 'ar' => 'رجال'],
            ['code' => 'female', 'en' => 'Female', 'fr' => 'Femme', 'ar' => 'نساء'],
            ['code' => 'unisex', 'en' => 'Unisex', 'fr' => 'Unisexe', 'ar' => 'للجنسين'],
        ];

        $this->upsert('genders', $rows);
    }

    private function seedSocialPlatforms(): void
    {
        $rows = [
            ['code' => 'facebook'],
            ['code' => 'instagram'],
            ['code' => 'tiktok'],
            ['code' => 'youtube'],
            ['code' => 'x'],
            ['code' => 'snapchat'],
        ];

        $this->upsert('social_platforms', $rows);
    }

    private function seedCategories(): void
    {
        $rows = [
            ['code' => 'clothing', 'en' => 'Clothing', 'fr' => 'Vêtements', 'ar' => 'ملابس'],
            ['code' => 'shoes', 'en' => 'Shoes', 'fr' => 'Chaussures', 'ar' => 'أحذية'],
            ['code' => 'bags', 'en' => 'Bags', 'fr' => 'Sacs', 'ar' => 'حقائب'],
            ['code' => 'accessories', 'en' => 'Accessories', 'fr' => 'Accessoires', 'ar' => 'إكسسوارات'],
            ['code' => 'watches', 'en' => 'Watches', 'fr' => 'Montres', 'ar' => 'ساعات'],
        ];

        $this->upsert('categories', $rows);
    }

    private function seedSizes(): void
    {
        $categories = DB::table('categories')->pluck('id', 'code');

        $rows = [];

        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size) {
            $rows[] = [
                'category_id' => $categories['clothing'],
                'code' => $size,
                'type' => 'clothing',
                'en' => $size,
                'fr' => $size,
                'ar' => $size,
            ];
        }

        foreach (range(36, 45) as $size) {
            $rows[] = [
                'category_id' => $categories['shoes'],
                'code' => (string) $size,
                'type' => 'shoes',
                'en' => (string) $size,
                'fr' => (string) $size,
                'ar' => (string) $size,
            ];
        }

        foreach (['one_size'] as $size) {
            foreach (['bags', 'accessories', 'watches'] as $categoryCode) {
                $rows[] = [
                    'category_id' => $categories[$categoryCode],
                    'code' => $size,
                    'type' => 'universal',
                    'en' => 'One Size',
                    'fr' => 'Taille unique',
                    'ar' => 'مقاس واحد',
                ];
            }
        }

        foreach ($rows as $row) {
            DB::table('sizes')->updateOrInsert(
                [
                    'category_id' => $row['category_id'],
                    'code' => $row['code'],
                ],
                array_merge($row, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }

    private function seedQualities(): void
    {
        $rows = [
            ['code' => 'original', 'en' => 'Original', 'fr' => 'Original', 'ar' => 'أصلي'],
            ['code' => 'copy', 'en' => 'Copy', 'fr' => 'Copie', 'ar' => 'نسخة'],
            ['code' => 'premium_copy', 'en' => 'Premium Copy', 'fr' => 'Copie premium', 'ar' => 'نسخة ممتازة'],
        ];

        $this->upsert('qualities', $rows);
    }

    private function upsert(string $table, array $rows): void
    {
        foreach ($rows as $row) {
            DB::table($table)->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
