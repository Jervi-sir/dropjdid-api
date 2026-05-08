<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupTableSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        DB::table('roles')->insert(array_map(
            fn (array $role): array => [...$role, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'user', 'description' => 'user', 'en' => 'User', 'fr' => 'Utilisateur', 'ar' => null],
                ['code' => 'creator', 'description' => 'creator', 'en' => 'Creator', 'fr' => 'Créateur', 'ar' => null],
                ['code' => 'sgm', 'description' => 'sgm', 'en' => 'SGM', 'fr' => 'SGM', 'ar' => null],
            ],
        ));

        DB::table('wilayas')->insert(array_map(
            fn (array $wilaya): array => [...$wilaya, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'adrar', 'number' => '01', 'en' => 'Adrar', 'fr' => 'Adrar', 'ar' => 'أدرار'],
                ['code' => 'chlef', 'number' => '02', 'en' => 'Chlef', 'fr' => 'Chlef', 'ar' => 'الشلف'],
                ['code' => 'laghouat', 'number' => '03', 'en' => 'Laghouat', 'fr' => 'Laghouat', 'ar' => 'الأغواط'],
                ['code' => 'oum_el_bouaghi', 'number' => '04', 'en' => 'Oum El Bouaghi', 'fr' => 'Oum El Bouaghi', 'ar' => 'أم البواقي'],
                ['code' => 'batna', 'number' => '05', 'en' => 'Batna', 'fr' => 'Batna', 'ar' => 'باتنة'],
                ['code' => 'bejaia', 'number' => '06', 'en' => 'Bejaia', 'fr' => 'Bejaia', 'ar' => 'بجاية'],
                ['code' => 'biskra', 'number' => '07', 'en' => 'Biskra', 'fr' => 'Biskra', 'ar' => 'بسكرة'],
                ['code' => 'bechar', 'number' => '08', 'en' => 'Bechar', 'fr' => 'Bechar', 'ar' => 'بشار'],
                ['code' => 'blida', 'number' => '09', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة'],
                ['code' => 'bouira', 'number' => '10', 'en' => 'Bouira', 'fr' => 'Bouira', 'ar' => 'البويرة'],
                ['code' => 'tamanrasset', 'number' => '11', 'en' => 'Tamanrasset', 'fr' => 'Tamanrasset', 'ar' => 'تمنراست'],
                ['code' => 'tebessa', 'number' => '12', 'en' => 'Tebessa', 'fr' => 'Tebessa', 'ar' => 'تبسة'],
                ['code' => 'tlemcen', 'number' => '13', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
                ['code' => 'tiaret', 'number' => '14', 'en' => 'Tiaret', 'fr' => 'Tiaret', 'ar' => 'تيارت'],
                ['code' => 'tizi_ouzou', 'number' => '15', 'en' => 'Tizi Ouzou', 'fr' => 'Tizi Ouzou', 'ar' => 'تيزي وزو'],
                ['code' => 'algiers', 'number' => '16', 'en' => 'Algiers', 'fr' => 'Alger', 'ar' => 'الجزائر'],
                ['code' => 'djelfa', 'number' => '17', 'en' => 'Djelfa', 'fr' => 'Djelfa', 'ar' => 'الجلفة'],
                ['code' => 'jijel', 'number' => '18', 'en' => 'Jijel', 'fr' => 'Jijel', 'ar' => 'جيجل'],
                ['code' => 'setif', 'number' => '19', 'en' => 'Setif', 'fr' => 'Setif', 'ar' => 'سطيف'],
                ['code' => 'saida', 'number' => '20', 'en' => 'Saida', 'fr' => 'Saida', 'ar' => 'سعيدة'],
                ['code' => 'skikda', 'number' => '21', 'en' => 'Skikda', 'fr' => 'Skikda', 'ar' => 'سكيكدة'],
                ['code' => 'sidi_bel_abbes', 'number' => '22', 'en' => 'Sidi Bel Abbes', 'fr' => 'Sidi Bel Abbes', 'ar' => 'سيدي بلعباس'],
                ['code' => 'annaba', 'number' => '23', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
                ['code' => 'guelma', 'number' => '24', 'en' => 'Guelma', 'fr' => 'Guelma', 'ar' => 'قالمة'],
                ['code' => 'constantine', 'number' => '25', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
                ['code' => 'medea', 'number' => '26', 'en' => 'Medea', 'fr' => 'Medea', 'ar' => 'المدية'],
                ['code' => 'mostaganem', 'number' => '27', 'en' => 'Mostaganem', 'fr' => 'Mostaganem', 'ar' => 'مستغانم'],
                ['code' => 'msila', 'number' => '28', 'en' => 'M\'Sila', 'fr' => 'M\'Sila', 'ar' => 'المسيلة'],
                ['code' => 'mascara', 'number' => '29', 'en' => 'Mascara', 'fr' => 'Mascara', 'ar' => 'معسكر'],
                ['code' => 'ouargla', 'number' => '30', 'en' => 'Ouargla', 'fr' => 'Ouargla', 'ar' => 'ورقلة'],
                ['code' => 'oran', 'number' => '31', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران'],
                ['code' => 'el_bayadh', 'number' => '32', 'en' => 'El Bayadh', 'fr' => 'El Bayadh', 'ar' => 'البيض'],
                ['code' => 'illizi', 'number' => '33', 'en' => 'Illizi', 'fr' => 'Illizi', 'ar' => 'اليزي'],
                ['code' => 'bordj_bou_arreridj', 'number' => '34', 'en' => 'Bordj Bou Arreridj', 'fr' => 'Bordj Bou Arreridj', 'ar' => 'برج بوعريريج'],
                ['code' => 'boumerdes', 'number' => '35', 'en' => 'Boumerdes', 'fr' => 'Boumerdes', 'ar' => 'بومرداس'],
                ['code' => 'el_tarf', 'number' => '36', 'en' => 'El Tarf', 'fr' => 'El Tarf', 'ar' => 'الطارف'],
                ['code' => 'tindouf', 'number' => '37', 'en' => 'Tindouf', 'fr' => 'Tindouf', 'ar' => 'تندوف'],
                ['code' => 'tissemsilt', 'number' => '38', 'en' => 'Tissemsilt', 'fr' => 'Tissemsilt', 'ar' => 'تسمسيلت'],
                ['code' => 'el_oued', 'number' => '39', 'en' => 'El Oued', 'fr' => 'El Oued', 'ar' => 'الوادي'],
                ['code' => 'khenchela', 'number' => '40', 'en' => 'Khenchela', 'fr' => 'Khenchela', 'ar' => 'خنشلة'],
                ['code' => 'souk_ahras', 'number' => '41', 'en' => 'Souk Ahras', 'fr' => 'Souk Ahras', 'ar' => 'سوق أهراس'],
                ['code' => 'tipaza', 'number' => '42', 'en' => 'Tipaza', 'fr' => 'Tipaza', 'ar' => 'تيبازة'],
                ['code' => 'mila', 'number' => '43', 'en' => 'Mila', 'fr' => 'Mila', 'ar' => 'ميلة'],
                ['code' => 'ain_defla', 'number' => '44', 'en' => 'Ain Defla', 'fr' => 'Ain Defla', 'ar' => 'عين الدفلى'],
                ['code' => 'naama', 'number' => '45', 'en' => 'Naama', 'fr' => 'Naama', 'ar' => 'النعامة'],
                ['code' => 'ain_temouchent', 'number' => '46', 'en' => 'Ain Temouchent', 'fr' => 'Ain Temouchent', 'ar' => 'عين تموشنت'],
                ['code' => 'ghardaia', 'number' => '47', 'en' => 'Ghardaia', 'fr' => 'Ghardaia', 'ar' => 'غرداية'],
                ['code' => 'relizane', 'number' => '48', 'en' => 'Relizane', 'fr' => 'Relizane', 'ar' => 'غليزان'],
                ['code' => 'timimoun', 'number' => '49', 'en' => 'Timimoun', 'fr' => 'Timimoun', 'ar' => 'تيميمون'],
                ['code' => 'bordj_badji_mokhtar', 'number' => '50', 'en' => 'Bordj Badji Mokhtar', 'fr' => 'Bordj Badji Mokhtar', 'ar' => 'برج باجي مختار'],
                ['code' => 'ouled_djellal', 'number' => '51', 'en' => 'Ouled Djellal', 'fr' => 'Ouled Djellal', 'ar' => 'أولاد جلال'],
                ['code' => 'beni_abbes', 'number' => '52', 'en' => 'Beni Abbes', 'fr' => 'Beni Abbes', 'ar' => 'بني عباس'],
                ['code' => 'in_salah', 'number' => '53', 'en' => 'In Salah', 'fr' => 'In Salah', 'ar' => 'عين صالح'],
                ['code' => 'in_guezzam', 'number' => '54', 'en' => 'In Guezzam', 'fr' => 'In Guezzam', 'ar' => 'عين قزام'],
                ['code' => 'touggourt', 'number' => '55', 'en' => 'Touggourt', 'fr' => 'Touggourt', 'ar' => 'تقرت'],
                ['code' => 'djanet', 'number' => '56', 'en' => 'Djanet', 'fr' => 'Djanet', 'ar' => 'جانت'],
                ['code' => 'el_mghair', 'number' => '57', 'en' => 'El M\'Ghair', 'fr' => 'El M\'Ghair', 'ar' => 'المغير'],
                ['code' => 'el_meniaa', 'number' => '58', 'en' => 'El Meniaa', 'fr' => 'El Meniaa', 'ar' => 'المنيعة'],
                ['code' => 'aflou', 'number' => '59', 'en' => 'Aflou', 'fr' => 'Aflou', 'ar' => null],
                ['code' => 'barika', 'number' => '60', 'en' => 'Barika', 'fr' => 'Barika', 'ar' => null],
                ['code' => 'ksar_chellala', 'number' => '61', 'en' => 'Ksar Chellala', 'fr' => 'Ksar Chellala', 'ar' => null],
                ['code' => 'messaad', 'number' => '62', 'en' => 'Messaad', 'fr' => 'Messaad', 'ar' => null],
                ['code' => 'ain_oussera', 'number' => '63', 'en' => 'Ain Oussera', 'fr' => 'Ain Oussera', 'ar' => null],
                ['code' => 'boussaada', 'number' => '64', 'en' => 'Boussaada', 'fr' => 'Boussaada', 'ar' => null],
                ['code' => 'el_abiodh_sidi_cheikh', 'number' => '65', 'en' => 'El Abiodh Sidi Cheikh', 'fr' => 'El Abiodh Sidi Cheikh', 'ar' => null],
                ['code' => 'el_kantara', 'number' => '66', 'en' => 'El Kantara', 'fr' => 'El Kantara', 'ar' => null],
                ['code' => 'bir_el_ater', 'number' => '67', 'en' => 'Bir El Ater', 'fr' => 'Bir El Ater', 'ar' => null],
                ['code' => 'ksar_el_boukhari', 'number' => '68', 'en' => 'Ksar El Boukhari', 'fr' => 'Ksar El Boukhari', 'ar' => null],
            ],
        ));

        DB::table('payment_methods')->insert(array_map(
            fn (array $method): array => [...$method, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'cod', 'en' => 'Cash on Delivery', 'fr' => 'Paiement a la livraison', 'ar' => null],
                ['code' => 'online', 'en' => 'Online Payment', 'fr' => 'Paiement en ligne', 'ar' => null],
            ],
        ));

        DB::table('genders')->insert(array_map(
            fn (array $gender): array => [...$gender, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'male', 'en' => 'Male', 'fr' => 'Homme', 'ar' => null],
                ['code' => 'female', 'en' => 'Female', 'fr' => 'Femme', 'ar' => null],
                ['code' => 'kid', 'en' => 'Kid', 'fr' => 'Enfant', 'ar' => null],
                ['code' => 'unisex', 'en' => 'Unisex', 'fr' => 'Unisexe', 'ar' => null],
            ],
        ));

        DB::table('social_platforms')->insert(array_map(
            fn (string $platform): array => [
                'code' => $platform,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            ['facebook', 'instagram', 'tiktok', 'youtube', 'snapchat', 'x', 'linkedin', 'pinterest', 'telegram', 'whatsapp'],
        ));

        $categories = collect([
            ['code' => 'wears_in_head', 'en' => 'Wears in Head', 'fr' => 'Vetements de tete', 'ar' => null],
            ['code' => 'upper_body', 'en' => 'Upper Body', 'fr' => 'Haut du corps', 'ar' => null],
            ['code' => 'bottom_body', 'en' => 'Bottom Body', 'fr' => 'Bas du corps', 'ar' => null],
            ['code' => 'wears_in_feet', 'en' => 'Wears in Feet', 'fr' => 'Chaussures', 'ar' => null],
            ['code' => 'bags', 'en' => 'Bags', 'fr' => 'Sacs', 'ar' => null],
            ['code' => 'outfits', 'en' => 'Outfits', 'fr' => 'Tenues', 'ar' => null],
            ['code' => 'accessories', 'en' => 'Accessories', 'fr' => 'Accessoires', 'ar' => null],
        ]);

        DB::table('categories')->insert($categories
            ->map(fn (array $category): array => [...$category, 'created_at' => $timestamp, 'updated_at' => $timestamp])
            ->all());

        $categoryIds = DB::table('categories')->pluck('id', 'code');

        $sizeGroups = [
            'wears_in_head' => ['XS', 'S', 'M', 'L', 'XL'],
            'upper_body' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'bottom_body' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'wears_in_feet' => ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46'],
            'bags' => ['ONE_SIZE'],
            'outfits' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'accessories' => ['ONE_SIZE'],
        ];

        $sizes = [];

        foreach ($sizeGroups as $categoryCode => $codes) {
            foreach ($codes as $code) {
                $sizes[] = [
                    'category_id' => $categoryIds[$categoryCode],
                    'code' => $code,
                    'type' => ctype_digit($code) ? 'numeric' : ($code === 'ONE_SIZE' ? 'universal' : 'clothing'),
                    'en' => Str::of($code)->replace('_', ' ')->title()->toString(),
                    'fr' => Str::of($code)->replace('_', ' ')->title()->toString(),
                    'ar' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::table('sizes')->insert($sizes);

        DB::table('qualities')->insert(array_map(
            fn (array $quality): array => [...$quality, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'original', 'en' => 'Original', 'fr' => 'Original', 'ar' => null],
                ['code' => 'copy', 'en' => 'Copy', 'fr' => 'Copie', 'ar' => null],
                ['code' => 'premium_copy', 'en' => 'Premium Copy', 'fr' => 'Copie Premium', 'ar' => null],
            ],
        ));

        DB::table('notification_types')->insert(array_map(
            fn (array $type): array => [...$type, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            [
                ['code' => 'sales', 'en' => 'Sales', 'fr' => 'Ventes', 'ar' => null],
                ['code' => 'withdraw', 'en' => 'Withdraw', 'fr' => 'Retrait', 'ar' => null],
                ['code' => 'tracking_order', 'en' => 'Tracking Order', 'fr' => 'Suivi de commande', 'ar' => null],
                ['code' => 'friend_request', 'en' => 'Friend Request', 'fr' => 'Demande d\'ami', 'ar' => null],
                ['code' => 'followers', 'en' => 'Followers', 'fr' => 'Abonnes', 'ar' => null],
            ],
        ));
    }
}
