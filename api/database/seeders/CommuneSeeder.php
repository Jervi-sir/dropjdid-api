<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Representative communes mapping for Algerian Wilayas
        $communesMap = [
            '16' => [ // Algiers
                ['code' => 'sidi_mhamed', 'post_code' => '16000', 'en' => "Sidi M'Hamed", 'fr' => "Sidi M'Hamed", 'ar' => 'سيدي امحمد'],
                ['code' => 'bab_el_oued', 'post_code' => '16008', 'en' => 'Bab El Oued', 'fr' => 'Bab El Oued', 'ar' => 'باب الوادي'],
                ['code' => 'hydra', 'post_code' => '16035', 'en' => 'Hydra', 'fr' => 'Hydra', 'ar' => 'حيدرة'],
                ['code' => 'kouba', 'post_code' => '16050', 'en' => 'Kouba', 'fr' => 'Kouba', 'ar' => 'القبة'],
                ['code' => 'bir_mourad_rais', 'post_code' => '16025', 'en' => 'Bir Mourad Raïs', 'fr' => 'Bir Mourad Raïs', 'ar' => 'بئر مراد رايس'],
                ['code' => 'ben_aknoun', 'post_code' => '16028', 'en' => 'Ben Aknoun', 'fr' => 'Ben Aknoun', 'ar' => 'بن عكنون'],
                ['code' => 'el_harrach', 'post_code' => '16200', 'en' => 'El Harrach', 'fr' => 'El Harrach', 'ar' => 'الحراش'],
                ['code' => 'dar_el_beida', 'post_code' => '16100', 'en' => 'Dar El Beïda', 'fr' => 'Dar El Beïda', 'ar' => 'الدار البيضاء'],
                ['code' => 'zeralda', 'post_code' => '16180', 'en' => 'Zeralda', 'fr' => 'Zeralda', 'ar' => 'زرالدة'],
                ['code' => 'staoueli', 'post_code' => '16101', 'en' => 'Staoueli', 'fr' => 'Staoueli', 'ar' => 'سطاوالي'],
                ['code' => 'cheraga', 'post_code' => '16014', 'en' => 'Cheraga', 'fr' => 'Chéraga', 'ar' => 'الشراقة'],
                ['code' => 'dely_brahim', 'post_code' => '16047', 'en' => 'Dely Ibrahim', 'fr' => 'Dely Ibrahim', 'ar' => 'دالي إبراهيم'],
                ['code' => 'hussein_dey', 'post_code' => '16040', 'en' => 'Hussein Dey', 'fr' => 'Hussein Dey', 'ar' => 'حسين داي'],
                ['code' => 'birkhadem', 'post_code' => '16029', 'en' => 'Birkhadem', 'fr' => 'Birkhadem', 'ar' => 'بئر خادم'],
                ['code' => 'ain_benian', 'post_code' => '16202', 'en' => 'Ain Benian', 'fr' => 'Aïn Bénian', 'ar' => 'عين البنيان'],
                ['code' => 'bordj_el_kiffan', 'post_code' => '16110', 'en' => 'Bordj El Kiffan', 'fr' => 'Bordj El Kiffan', 'ar' => 'برج الكيفان'],
                ['code' => 'rouiba', 'post_code' => '16012', 'en' => 'Rouiba', 'fr' => 'Rouïba', 'ar' => 'الرويبة'],
                ['code' => 'reghaia', 'post_code' => '16036', 'en' => 'Reghaia', 'fr' => 'Réghaïa', 'ar' => 'رغاية'],
            ],
            '31' => [ // Oran
                ['code' => 'oran_centre', 'post_code' => '31000', 'en' => 'Oran Centre', 'fr' => 'Oran Centre', 'ar' => 'وهران'],
                ['code' => 'es_senia', 'post_code' => '31100', 'en' => 'Es Senia', 'fr' => 'Es Senia', 'ar' => 'السانية'],
                ['code' => 'bir_el_djir', 'post_code' => '31130', 'en' => 'Bir El Djir', 'fr' => 'Bir El Djir', 'ar' => 'بئر الجير'],
                ['code' => 'sidi_chami', 'post_code' => '31017', 'en' => 'Sidi Chami', 'fr' => 'Sidi Chami', 'ar' => 'سيدي الشحمي'],
                ['code' => 'arzew', 'post_code' => '31200', 'en' => 'Arzew', 'fr' => 'Arzew', 'ar' => 'أرزيو'],
                ['code' => 'ain_el_turck', 'post_code' => '31300', 'en' => 'Ain El Turck', 'fr' => 'Aïn El Turck', 'ar' => 'عين الترك'],
                ['code' => 'bethioua', 'post_code' => '31210', 'en' => 'Bethioua', 'fr' => 'Bethioua', 'ar' => 'بطيوة'],
            ],
            '09' => [ // Blida
                ['code' => 'blida_centre', 'post_code' => '09000', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة'],
                ['code' => 'boufarik', 'post_code' => '09400', 'en' => 'Boufarik', 'fr' => 'Boufarik', 'ar' => 'بوفاريك'],
                ['code' => 'ouled_yaich', 'post_code' => '09001', 'en' => 'Ouled Yaich', 'fr' => 'Ouled Yaïch', 'ar' => 'أولاد يعيش'],
                ['code' => 'el_affroun', 'post_code' => '09200', 'en' => 'El Affroun', 'fr' => 'El Affroun', 'ar' => 'العفرون'],
                ['code' => 'mouzaia', 'post_code' => '09100', 'en' => 'Mouzaia', 'fr' => 'Mouzaïa', 'ar' => 'موزاية'],
                ['code' => 'larbaa', 'post_code' => '09300', 'en' => 'Larbaa', 'fr' => "Larbaâ", 'ar' => 'الأربعاء'],
            ],
            '25' => [ // Constantine
                ['code' => 'constantine_centre', 'post_code' => '25000', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
                ['code' => 'el_khroub', 'post_code' => '25100', 'en' => 'El Khroub', 'fr' => 'El Khroub', 'ar' => 'الخروب'],
                ['code' => 'ain_smara', 'post_code' => '25140', 'en' => 'Ain Smara', 'fr' => 'Aïn Smara', 'ar' => 'عين سمارة'],
                ['code' => 'hamma_bouziane', 'post_code' => '25200', 'en' => 'Hamma Bouziane', 'fr' => 'Hamma Bouziane', 'ar' => 'حامة بوزيان'],
                ['code' => 'ali_mendjeli', 'post_code' => '25016', 'en' => 'Ali Mendjeli', 'fr' => 'Ali Mendjeli', 'ar' => 'علي منجلي'],
            ],
            '19' => [ // Setif
                ['code' => 'setif_centre', 'post_code' => '19000', 'en' => 'Setif', 'fr' => 'Sétif', 'ar' => 'سطيف'],
                ['code' => 'el_eulma', 'post_code' => '19600', 'en' => 'El Eulma', 'fr' => 'El Eulma', 'ar' => 'العلمة'],
                ['code' => 'ain_oulmene', 'post_code' => '19200', 'en' => 'Ain Oulmene', 'fr' => 'Aïn Oulmène', 'ar' => 'عين ولمان'],
                ['code' => 'ain_arnat', 'post_code' => '19002', 'en' => 'Ain Arnat', 'fr' => 'Aïn Arnat', 'ar' => 'عين أرنات'],
            ],
            '23' => [ // Annaba
                ['code' => 'annaba_centre', 'post_code' => '23000', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
                ['code' => 'el_bouni', 'post_code' => '23005', 'en' => 'El Bouni', 'fr' => 'El Bouni', 'ar' => 'البوني'],
                ['code' => 'el_hadjar', 'post_code' => '23200', 'en' => 'El Hadjar', 'fr' => 'El Hadjar', 'ar' => 'الحجار'],
                ['code' => 'sidi_amar', 'post_code' => '23002', 'en' => 'Sidi Amar', 'fr' => 'Sidi Amar', 'ar' => 'سيدي عمار'],
            ],
            '15' => [ // Tizi Ouzou
                ['code' => 'tizi_ouzou_centre', 'post_code' => '15000', 'en' => 'Tizi Ouzou', 'fr' => 'Tizi Ouzou', 'ar' => 'تيزي وزو'],
                ['code' => 'draa_ben_khedda', 'post_code' => '15100', 'en' => 'Draa Ben Khedda', 'fr' => 'Draâ Ben Khedda', 'ar' => 'ذراع بن خدة'],
                ['code' => 'azazga', 'post_code' => '15300', 'en' => 'Azazga', 'fr' => 'Azazga', 'ar' => 'عزازقة'],
                ['code' => 'boghhni', 'post_code' => '15400', 'en' => 'Boghni', 'fr' => 'Boghni', 'ar' => 'بوغني'],
            ],
            '06' => [ // Bejaia
                ['code' => 'bejaia_centre', 'post_code' => '06000', 'en' => 'Bejaia', 'fr' => 'Béjaïa', 'ar' => 'بجاية'],
                ['code' => 'akbou', 'post_code' => '06200', 'en' => 'Akbou', 'fr' => 'Akbou', 'ar' => 'أقبو'],
                ['code' => 'amizour', 'post_code' => '06300', 'en' => 'Amizour', 'fr' => 'Amizour', 'ar' => 'أميزور'],
                ['code' => 'el_kseur', 'post_code' => '06100', 'en' => 'El Kseur', 'fr' => 'El Kseur', 'ar' => 'القصر'],
            ],
            '35' => [ // Boumerdes
                ['code' => 'boumerdes_centre', 'post_code' => '35000', 'en' => 'Boumerdes', 'fr' => 'Boumerdès', 'ar' => 'بومرداس'],
                ['code' => 'khemis_el_khechna', 'post_code' => '35100', 'en' => 'Khemis El Khechna', 'fr' => 'Khemis El Khechna', 'ar' => 'خميس الخشنة'],
                ['code' => 'bordj_menaiel', 'post_code' => '35200', 'en' => 'Bordj Menaiel', 'fr' => 'Bordj Menaïel', 'ar' => 'برج منايل'],
                ['code' => 'dellys', 'post_code' => '35002', 'en' => 'Dellys', 'fr' => 'Dellys', 'ar' => 'دلس'],
            ],
            '42' => [ // Tipaza
                ['code' => 'tipaza_centre', 'post_code' => '42000', 'en' => 'Tipaza', 'fr' => 'Tipaza', 'ar' => 'تيبازة'],
                ['code' => 'kolea', 'post_code' => '42400', 'en' => 'Kolea', 'fr' => 'Koléa', 'ar' => 'القليعة'],
                ['code' => 'fouka', 'post_code' => '42440', 'en' => 'Fouka', 'fr' => 'Fouka', 'ar' => 'فوكة'],
                ['code' => 'cherchell', 'post_code' => '42100', 'en' => 'Cherchell', 'fr' => 'Cherchell', 'ar' => 'شرشال'],
                ['code' => 'bou_ismail', 'post_code' => '42415', 'en' => 'Bou Ismail', 'fr' => 'Bou Ismaïl', 'ar' => 'بواسماعيل'],
            ],
            '13' => [ // Tlemcen
                ['code' => 'tlemcen_centre', 'post_code' => '13000', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
                ['code' => 'mansourah', 'post_code' => '13001', 'en' => 'Mansourah', 'fr' => 'Mansourah', 'ar' => 'منصورة'],
                ['code' => 'maghnia', 'post_code' => '13300', 'en' => 'Maghnia', 'fr' => 'Maghnia', 'ar' => 'مغنية'],
                ['code' => 'ghazaouet', 'post_code' => '13400', 'en' => 'Ghazaouet', 'fr' => 'Ghazaouet', 'ar' => 'الغزوات'],
            ],
        ];

        // Seed explicit communes for prominent wilayas
        foreach ($communesMap as $wilayaNumber => $communes) {
            $wilaya = Wilaya::where('number', $wilayaNumber)->first();
            if (! $wilaya) continue;

            foreach ($communes as $commune) {
                Commune::firstOrCreate([
                    'wilaya_id' => $wilaya->id,
                    'code' => $commune['code'],
                ], array_merge($commune, ['wilaya_id' => $wilaya->id]));
            }
        }

        // For all other wilayas, ensure at least one centre commune exists
        $allWilayas = Wilaya::all();
        foreach ($allWilayas as $wilaya) {
            $count = Commune::where('wilaya_id', $wilaya->id)->count();
            if ($count === 0) {
                Commune::firstOrCreate([
                    'wilaya_id' => $wilaya->id,
                    'code' => "{$wilaya->code}_centre",
                ], [
                    'wilaya_id' => $wilaya->id,
                    'code' => "{$wilaya->code}_centre",
                    'post_code' => "{$wilaya->number}000",
                    'en' => $wilaya->en . ' Centre',
                    'fr' => $wilaya->fr . ' Centre',
                    'ar' => $wilaya->ar . ' المركز',
                ]);
            }
        }
    }
}
