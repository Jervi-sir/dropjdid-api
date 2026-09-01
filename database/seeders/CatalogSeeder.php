<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Gender;
use App\Models\OrderStatus;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Size;
use App\Models\SocialPlatform;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['code' => 'admin', 'en' => 'Admin', 'fr' => 'Administrateur', 'ar' => 'مشرف'],
            ['code' => 'creator', 'en' => 'Creator', 'fr' => 'Créateur', 'ar' => 'صانع محتوى'],
            ['code' => 'store', 'en' => 'Store Owner', 'fr' => 'Propriétaire de boutique', 'ar' => 'صاحب متجر'],
            ['code' => 'user', 'en' => 'Customer', 'fr' => 'Client', 'ar' => 'مستخدم'],
            ['code' => 'sgm', 'en' => 'Store General Manager', 'fr' => 'Gérant de magasin', 'ar' => 'مدير متجر'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['code' => $role['code']], $role);
        }

        // 2. Genders
        $genders = [
            ['code' => 'men', 'en' => 'Men', 'fr' => 'Hommes', 'ar' => 'رجال'],
            ['code' => 'women', 'en' => 'Women', 'fr' => 'Femmes', 'ar' => 'نساء'],
            ['code' => 'unisex', 'en' => 'Unisex', 'fr' => 'Unisexe', 'ar' => 'للجنسين'],
            ['code' => 'kids', 'en' => 'Kids', 'fr' => 'Enfants', 'ar' => 'أطفال'],
        ];

        foreach ($genders as $gender) {
            Gender::firstOrCreate(['code' => $gender['code']], $gender);
        }

        // 3. Social Platforms
        $socialPlatforms = [
            ['code' => 'facebook', 'label' => 'Facebook', 'hex' => '#1877F2', 'badge' => 'FB'],
            ['code' => 'instagram', 'label' => 'Instagram', 'hex' => '#E1306C', 'badge' => 'IG'],
            ['code' => 'tiktok', 'label' => 'TikTok', 'hex' => '#000000', 'badge' => 'TT'],
            ['code' => 'whatsapp', 'label' => 'WhatsApp', 'hex' => '#25D366', 'badge' => 'WA'],
            ['code' => 'telegram', 'label' => 'Telegram', 'hex' => '#229ED9', 'badge' => 'TG'],
            ['code' => 'youtube', 'label' => 'YouTube', 'hex' => '#FF0000', 'badge' => 'YT'],
            ['code' => 'snapchat', 'label' => 'Snapchat', 'hex' => '#FFFC00', 'badge' => 'SC'],
            ['code' => 'x', 'label' => 'X (Twitter)', 'hex' => '#111111', 'badge' => 'X'],
            ['code' => 'linkedin', 'label' => 'LinkedIn', 'hex' => '#0A66C2', 'badge' => 'IN'],
            ['code' => 'website', 'label' => 'Website', 'hex' => '#3B82F6', 'badge' => 'WEB'],
            ['code' => 'phone', 'label' => 'Phone', 'hex' => '#10B981', 'badge' => 'TEL'],
            ['code' => 'email', 'label' => 'Email', 'hex' => '#F59E0B', 'badge' => '@'],
            ['code' => 'other', 'label' => 'Other', 'hex' => '#333333', 'badge' => 'OT'],
        ];

        foreach ($socialPlatforms as $platform) {
            SocialPlatform::updateOrCreate(['code' => $platform['code']], $platform);
        }


        // 4. Qualities
        $qualities = [
            ['code' => 'original', 'en' => 'Original', 'fr' => 'Original', 'ar' => 'أصلي'],
            ['code' => 'premium_copy', 'en' => 'Premium Copy', 'fr' => 'Copie Premium', 'ar' => 'نسخة ممتازة'],
            ['code' => 'copy', 'en' => 'Copy', 'fr' => 'Copie', 'ar' => 'نسخة'],
        ];

        foreach ($qualities as $quality) {
            Quality::firstOrCreate(['code' => $quality['code']], $quality);
        }

        // 5. Categories & Sizes
        $categoriesWithSizes = [
            [
                'category' => ['code' => 'clothing', 'en' => 'Clothing', 'fr' => 'Vêtements', 'ar' => 'ملابس'],
                'sizes' => [
                    ['code' => 'XS', 'type' => 'clothing', 'en' => 'Extra Small', 'fr' => 'Très Petit', 'ar' => 'صغير جداً'],
                    ['code' => 'S', 'type' => 'clothing', 'en' => 'Small', 'fr' => 'Petit', 'ar' => 'صغير'],
                    ['code' => 'M', 'type' => 'clothing', 'en' => 'Medium', 'fr' => 'Moyen', 'ar' => 'متوسط'],
                    ['code' => 'L', 'type' => 'clothing', 'en' => 'Large', 'fr' => 'Grand', 'ar' => 'كبير'],
                    ['code' => 'XL', 'type' => 'clothing', 'en' => 'Extra Large', 'fr' => 'Très Grand', 'ar' => 'كبير جداً'],
                    ['code' => 'XXL', 'type' => 'clothing', 'en' => 'Double Extra Large', 'fr' => 'Double Très Grand', 'ar' => 'كبير مضاعف'],
                    ['code' => '3XL', 'type' => 'clothing', 'en' => 'Triple Extra Large', 'fr' => 'Triple Très Grand', 'ar' => 'كبير جداً 3XL'],
                ],
            ],
            [
                'category' => ['code' => 'shoes', 'en' => 'Shoes', 'fr' => 'Chaussures', 'ar' => 'أحذية'],
                'sizes' => [
                    ['code' => '38', 'type' => 'shoes', 'en' => 'Size 38', 'fr' => 'Pointure 38', 'ar' => 'مقاس 38'],
                    ['code' => '39', 'type' => 'shoes', 'en' => 'Size 39', 'fr' => 'Pointure 39', 'ar' => 'مقاس 39'],
                    ['code' => '40', 'type' => 'shoes', 'en' => 'Size 40', 'fr' => 'Pointure 40', 'ar' => 'مقاس 40'],
                    ['code' => '41', 'type' => 'shoes', 'en' => 'Size 41', 'fr' => 'Pointure 41', 'ar' => 'مقاس 41'],
                    ['code' => '42', 'type' => 'shoes', 'en' => 'Size 42', 'fr' => 'Pointure 42', 'ar' => 'مقاس 42'],
                    ['code' => '43', 'type' => 'shoes', 'en' => 'Size 43', 'fr' => 'Pointure 43', 'ar' => 'مقاس 43'],
                    ['code' => '44', 'type' => 'shoes', 'en' => 'Size 44', 'fr' => 'Pointure 44', 'ar' => 'مقاس 44'],
                    ['code' => '45', 'type' => 'shoes', 'en' => 'Size 45', 'fr' => 'Pointure 45', 'ar' => 'مقاس 45'],
                ],
            ],
            [
                'category' => ['code' => 'accessories', 'en' => 'Accessories', 'fr' => 'Accessoires', 'ar' => 'إكسسوارات'],
                'sizes' => [
                    ['code' => 'ONE_SIZE', 'type' => 'universal', 'en' => 'One Size', 'fr' => 'Taille Unique', 'ar' => 'مقاس موحد'],
                ],
            ],
            [
                'category' => ['code' => 'streetwear', 'en' => 'Streetwear', 'fr' => 'Streetwear', 'ar' => 'ستريت وير'],
                'sizes' => [
                    ['code' => 'S', 'type' => 'clothing', 'en' => 'Small', 'fr' => 'Petit', 'ar' => 'صغير'],
                    ['code' => 'M', 'type' => 'clothing', 'en' => 'Medium', 'fr' => 'Moyen', 'ar' => 'متوسط'],
                    ['code' => 'L', 'type' => 'clothing', 'en' => 'Large', 'fr' => 'Grand', 'ar' => 'كبير'],
                    ['code' => 'XL', 'type' => 'clothing', 'en' => 'Extra Large', 'fr' => 'Très Grand', 'ar' => 'كبير جداً'],
                    ['code' => 'XXL', 'type' => 'clothing', 'en' => 'Double Extra Large', 'fr' => 'Double Très Grand', 'ar' => 'كبير مضاعف'],
                ],
            ],
            [
                'category' => ['code' => 'sportswear', 'en' => 'Sportswear', 'fr' => 'Sportswear', 'ar' => 'ملابس رياضية'],
                'sizes' => [
                    ['code' => 'S', 'type' => 'clothing', 'en' => 'Small', 'fr' => 'Petit', 'ar' => 'صغير'],
                    ['code' => 'M', 'type' => 'clothing', 'en' => 'Medium', 'fr' => 'Moyen', 'ar' => 'متوسط'],
                    ['code' => 'L', 'type' => 'clothing', 'en' => 'Large', 'fr' => 'Grand', 'ar' => 'كبير'],
                    ['code' => 'XL', 'type' => 'clothing', 'en' => 'Extra Large', 'fr' => 'Très Grand', 'ar' => 'كبير جداً'],
                ],
            ],
        ];

        foreach ($categoriesWithSizes as $item) {
            $cat = Category::firstOrCreate(['code' => $item['category']['code']], $item['category']);

            foreach ($item['sizes'] as $sz) {
                Size::firstOrCreate([
                    'category_id' => $cat->id,
                    'code' => $sz['code'],
                ], array_merge($sz, ['category_id' => $cat->id]));
            }
        }

        // 6. Wilayas (58 Algerian Wilayas)
        $wilayas = [
            ['number' => '01', 'code' => 'adrar', 'en' => 'Adrar', 'fr' => 'Adrar', 'ar' => 'أدرار'],
            ['number' => '02', 'code' => 'chlef', 'en' => 'Chlef', 'fr' => 'Chlef', 'ar' => 'الشلف'],
            ['number' => '03', 'code' => 'laghouat', 'en' => 'Laghouat', 'fr' => 'Laghouat', 'ar' => 'الأغواط'],
            ['number' => '04', 'code' => 'oum_el_bouaghi', 'en' => 'Oum El Bouaghi', 'fr' => 'Oum El Bouaghi', 'ar' => 'أم البواقي'],
            ['number' => '05', 'code' => 'batna', 'en' => 'Batna', 'fr' => 'Batna', 'ar' => 'باتنة'],
            ['number' => '06', 'code' => 'bejaia', 'en' => 'Béjaïa', 'fr' => 'Béjaïa', 'ar' => 'بجاية'],
            ['number' => '07', 'code' => 'biskra', 'en' => 'Biskra', 'fr' => 'Biskra', 'ar' => 'بسكرة'],
            ['number' => '08', 'code' => 'bechar', 'en' => 'Béchar', 'fr' => 'Béchar', 'ar' => 'بشار'],
            ['number' => '09', 'code' => 'blida', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة'],
            ['number' => '10', 'code' => 'bouira', 'en' => 'Bouira', 'fr' => 'Bouira', 'ar' => 'البويرة'],
            ['number' => '11', 'code' => 'tamanrasset', 'en' => 'Tamanrasset', 'fr' => 'Tamanrasset', 'ar' => 'تمنراست'],
            ['number' => '12', 'code' => 'tebessa', 'en' => 'Tébessa', 'fr' => 'Tébessa', 'ar' => 'تبسة'],
            ['number' => '13', 'code' => 'tlemcen', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
            ['number' => '14', 'code' => 'tiaret', 'en' => 'Tiaret', 'fr' => 'Tiaret', 'ar' => 'تيارت'],
            ['number' => '15', 'code' => 'tizi_ouzou', 'en' => 'Tizi Ouzou', 'fr' => 'Tizi Ouzou', 'ar' => 'تيزي وزو'],
            ['number' => '16', 'code' => 'algiers', 'en' => 'Algiers', 'fr' => 'Alger', 'ar' => 'الجزائر'],
            ['number' => '17', 'code' => 'djelfa', 'en' => 'Djelfa', 'fr' => 'Djelfa', 'ar' => 'الجلفة'],
            ['number' => '18', 'code' => 'jijel', 'en' => 'Jijel', 'fr' => 'Jijel', 'ar' => 'جيجل'],
            ['number' => '19', 'code' => 'setif', 'en' => 'Sétif', 'fr' => 'Sétif', 'ar' => 'سطيف'],
            ['number' => '20', 'code' => 'saida', 'en' => 'Saïda', 'fr' => 'Saïda', 'ar' => 'سعيدة'],
            ['number' => '21', 'code' => 'skikda', 'en' => 'Skikda', 'fr' => 'Skikda', 'ar' => 'سكيكدة'],
            ['number' => '22', 'code' => 'sidi_bel_abbes', 'en' => 'Sidi Bel Abbès', 'fr' => 'Sidi Bel Abbès', 'ar' => 'سيدي بلعباس'],
            ['number' => '23', 'code' => 'annaba', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
            ['number' => '24', 'code' => 'guelma', 'en' => 'Guelma', 'fr' => 'Guelma', 'ar' => 'قالمة'],
            ['number' => '25', 'code' => 'constantine', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
            ['number' => '26', 'code' => 'medea', 'en' => 'Médéa', 'fr' => 'Médéa', 'ar' => 'المدية'],
            ['number' => '27', 'code' => 'mostaganem', 'en' => 'Mostaganem', 'fr' => 'Mostaganem', 'ar' => 'مستغانم'],
            ['number' => '28', 'code' => 'msila', 'en' => "M'Sila", 'fr' => "M'Sila", 'ar' => 'المسيلة'],
            ['number' => '29', 'code' => 'mascara', 'en' => 'Mascara', 'fr' => 'Mascara', 'ar' => 'معسكر'],
            ['number' => '30', 'code' => 'ouargla', 'en' => 'Ouargla', 'fr' => 'Ouargla', 'ar' => 'ورقلة'],
            ['number' => '31', 'code' => 'oran', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران'],
            ['number' => '32', 'code' => 'el_bayadh', 'en' => 'El Bayadh', 'fr' => 'El Bayadh', 'ar' => 'البيض'],
            ['number' => '33', 'code' => 'illizi', 'en' => 'Illizi', 'fr' => 'Illizi', 'ar' => 'إليزي'],
            ['number' => '34', 'code' => 'bordj_bou_arreridj', 'en' => 'Bordj Bou Arréridj', 'fr' => 'Bordj Bou Arréridj', 'ar' => 'برج بوعريريج'],
            ['number' => '35', 'code' => 'boumerdes', 'en' => 'Boumerdès', 'fr' => 'Boumerdès', 'ar' => 'بومرداس'],
            ['number' => '36', 'code' => 'el_tarf', 'en' => 'El Tarf', 'fr' => 'El Tarf', 'ar' => 'الطارف'],
            ['number' => '37', 'code' => 'tindouf', 'en' => 'Tindouf', 'fr' => 'Tindouf', 'ar' => 'تندوف'],
            ['number' => '38', 'code' => 'tissemsilt', 'en' => 'Tissemsilt', 'fr' => 'Tissemsilt', 'ar' => 'تيسمسيلت'],
            ['number' => '39', 'code' => 'el_oued', 'en' => 'El Oued', 'fr' => 'El Oued', 'ar' => 'الوادي'],
            ['number' => '40', 'code' => 'khenchela', 'en' => 'Khenchela', 'fr' => 'Khenchela', 'ar' => 'خنشلة'],
            ['number' => '41', 'code' => 'souk_ahras', 'en' => 'Souk Ahras', 'fr' => 'Souk Ahras', 'ar' => 'سوق أهراس'],
            ['number' => '42', 'code' => 'tipaza', 'en' => 'Tipaza', 'fr' => 'Tipaza', 'ar' => 'تيبازة'],
            ['number' => '43', 'code' => 'mila', 'en' => 'Mila', 'fr' => 'Mila', 'ar' => 'ميلة'],
            ['number' => '44', 'code' => 'ain_defla', 'en' => 'Aïn Defla', 'fr' => 'Aïn Defla', 'ar' => 'عين الدفلى'],
            ['number' => '45', 'code' => 'naama', 'en' => 'Naâma', 'fr' => 'Naâma', 'ar' => 'النعامة'],
            ['number' => '46', 'code' => 'ain_temouchent', 'en' => 'Aïn Témouchent', 'fr' => 'Aïn Témouchent', 'ar' => 'عين تموشنت'],
            ['number' => '47', 'code' => 'ghardaia', 'en' => 'Ghardaïa', 'fr' => 'Ghardaïa', 'ar' => 'غرداية'],
            ['number' => '48', 'code' => 'relizane', 'en' => 'Relizane', 'fr' => 'Relizane', 'ar' => 'غليزان'],
            ['number' => '49', 'code' => 'timimoun', 'en' => 'Timimoun', 'fr' => 'Timimoun', 'ar' => 'تيميمون'],
            ['number' => '50', 'code' => 'bordj_badji_mokhtar', 'en' => 'Bordj Badji Mokhtar', 'fr' => 'Bordj Badji Mokhtar', 'ar' => 'برج باجي مختار'],
            ['number' => '51', 'code' => 'ouled_djellal', 'en' => 'Ouled Djellal', 'fr' => 'Ouled Djellal', 'ar' => 'أولاد جلال'],
            ['number' => '52', 'code' => 'beni_abbes', 'en' => 'Béni Abbès', 'fr' => 'Béni Abbès', 'ar' => 'بني عباس'],
            ['number' => '53', 'code' => 'in_salah', 'en' => 'In Salah', 'fr' => 'In Salah', 'ar' => 'عين صالح'],
            ['number' => '54', 'code' => 'in_guezzam', 'en' => 'In Guezzam', 'fr' => 'In Guezzam', 'ar' => 'عين قزام'],
            ['number' => '55', 'code' => 'touggourt', 'en' => 'Touggourt', 'fr' => 'Touggourt', 'ar' => 'تقرت'],
            ['number' => '56', 'code' => 'djanet', 'en' => 'Djanet', 'fr' => 'Djanet', 'ar' => 'جانت'],
            ['number' => '57', 'code' => 'el_mghair', 'en' => "El M'Ghair", 'fr' => "El M'Ghair", 'ar' => 'المغير'],
            ['number' => '58', 'code' => 'el_meniaa', 'en' => 'El Meniaa', 'fr' => 'El Meniaa', 'ar' => 'المنيعة'],
        ];

        foreach ($wilayas as $wilaya) {
            Wilaya::firstOrCreate(['number' => $wilaya['number']], $wilaya);
        }

        // 7. Order Statuses
        $orderStatuses = [
            [
                'code' => OrderStatus::PENDING,
                'en' => 'Pending',
                'fr' => 'En attente',
                'ar' => 'قيد الانتظار',
                'color' => '#EAB308',
                'icon' => 'clock',
                'sort_order' => 1,
            ],
            [
                'code' => OrderStatus::CONFIRMED,
                'en' => 'Confirmed',
                'fr' => 'Confirmée',
                'ar' => 'مؤكدة',
                'color' => '#3B82F6',
                'icon' => 'check-circle',
                'sort_order' => 2,
            ],
            [
                'code' => OrderStatus::PROCESSING,
                'en' => 'Processing',
                'fr' => 'En préparation',
                'ar' => 'قيد التحضير',
                'color' => '#8B5CF6',
                'icon' => 'package',
                'sort_order' => 3,
            ],
            [
                'code' => OrderStatus::SHIPPED,
                'en' => 'Shipped',
                'fr' => 'Expédiée',
                'ar' => 'تم الشحن',
                'color' => '#06B6D4',
                'icon' => 'truck',
                'sort_order' => 4,
            ],
            [
                'code' => OrderStatus::DELIVERED,
                'en' => 'Delivered',
                'fr' => 'Livrée',
                'ar' => 'تم التوصيل',
                'color' => '#10B981',
                'icon' => 'check-double',
                'sort_order' => 5,
            ],
            [
                'code' => OrderStatus::CANCELLED,
                'en' => 'Cancelled',
                'fr' => 'Annulée',
                'ar' => 'ملغاة',
                'color' => '#EF4444',
                'icon' => 'x-circle',
                'sort_order' => 6,
            ],
            [
                'code' => OrderStatus::RETURNED,
                'en' => 'Returned',
                'fr' => 'Retournée',
                'ar' => 'مسترجعة',
                'color' => '#6B7280',
                'icon' => 'rotate-ccw',
                'sort_order' => 7,
            ],
        ];

        foreach ($orderStatuses as $status) {
            OrderStatus::firstOrCreate(['code' => $status['code']], $status);
        }
    }
}
