<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storeTemplates = [
            ['name' => 'DZ Streetwear Lab', 'city' => 'Algiers', 'desc' => 'Original sneakers, rare drops, and premium street apparel in Algeria.'],
            ['name' => 'Urban Kicks Algiers', 'city' => 'Algiers', 'desc' => 'Exclusive drops, authentic shoes, and streetwear accessories.'],
            ['name' => 'Vintage Drop Oran', 'city' => 'Oran', 'desc' => 'Curated vintage, 90s retro hoodies, and trending clothing items.'],
            ['name' => 'Constantine Trend Store', 'city' => 'Constantine', 'desc' => 'Top apparel brands, sportswear, and limited edition drops.'],
            ['name' => 'Hydra Drip Vault', 'city' => 'Algiers', 'desc' => 'High-end streetwear, luxury skatewear, and verified sneakers.'],
            ['name' => 'Casbah Thrift & Kicks', 'city' => 'Algiers', 'desc' => 'Authentic vintage finds, retro jackets, and classic sneakers.'],
            ['name' => 'Blida Sneaker Club', 'city' => 'Blida', 'desc' => 'Sneakerhead central for authentic Jordan, Nike, and Yeezy releases.'],
            ['name' => 'Annaba Hype Hub', 'city' => 'Annaba', 'desc' => 'Trending urban fashion, graphic tees, and streetwear collectibles.'],
            ['name' => 'Bab Ezzouar Fashion Lab', 'city' => 'Algiers', 'desc' => 'Modern lifestyle apparel, cargo pants, and oversized street fits.'],
            ['name' => 'Setif Outfits Corner', 'city' => 'Sétif', 'desc' => 'Premium street brands, hoodies, tracksuits, and caps.'],
            ['name' => 'Tlemcen Kicks & Drip', 'city' => 'Tlemcen', 'desc' => 'Original sneakers and fashionable streetwear imports.'],
            ['name' => 'Kouba Street Style', 'city' => 'Algiers', 'desc' => 'Urban drip, essential oversized garments, and trendy accessories.'],
            ['name' => 'Dely Ibrahim Closet', 'city' => 'Algiers', 'desc' => 'Curated collection of top tier designer and streetwear brands.'],
            ['name' => 'Wahran Apparel Co.', 'city' => 'Oran', 'desc' => 'Coastal streetwear vibes, graphic hoodies, and summer apparel.'],
            ['name' => 'Batna Drip House', 'city' => 'Batna', 'desc' => 'Everyday streetwear essentials, sneakers, and hype accessories.'],
            ['name' => 'Bejaia Coastline Kicks', 'city' => 'Béjaïa', 'desc' => 'Fresh streetwear, casual sneakers, and modern lifestyle drip.'],
            ['name' => 'Mostaganem Urban Wave', 'city' => 'Mostaganem', 'desc' => 'Youth street culture, cargo essentials, and high-demand drops.'],
            ['name' => 'Chlef Hype Store', 'city' => 'Chlef', 'desc' => 'Verified original streetwear, sneakers, and branded sportswear.'],
            ['name' => 'Biskra Palm Streetwear', 'city' => 'Biskra', 'desc' => 'Trending clothing, authentic footwear, and accessories.'],
            ['name' => 'Jijel Ocean Drip', 'city' => 'Jijel', 'desc' => 'Coastal streetwear fits, hoodies, oversized tees, and trainers.'],
            ['name' => 'Sidi Bel Abbes Sneaker Spot', 'city' => 'Sidi Bel Abbès', 'desc' => 'Authentic sneakers and imported street fashion.'],
            ['name' => 'Boumerdes Street Lab', 'city' => 'Boumerdès', 'desc' => 'Minimalist streetwear, caps, bags, and urban footwear.'],
            ['name' => 'Tizi Ouzou Trend Hub', 'city' => 'Tizi Ouzou', 'desc' => 'Modern youth fashion, tracksuits, and limited sneakers.'],
            ['name' => 'Tipaza Coastal Kicks', 'city' => 'Tipaza', 'desc' => 'Curated sneakers and relaxed streetwear clothing.'],
            ['name' => 'Bordj Bou Arreridj Hype', 'city' => 'Bordj Bou Arréridj', 'desc' => 'The premier spot for streetwear and modern apparel.'],
            ['name' => 'Skikda Port Drip', 'city' => 'Skikda', 'desc' => 'Trending urban outfits, skate apparel, and authentic shoes.'],
            ['name' => 'Mascara Vintage Room', 'city' => 'Mascara', 'desc' => 'Selected retro streetwear pieces, bomber jackets, and classics.'],
            ['name' => 'Guelma Fashion Club', 'city' => 'Guelma', 'desc' => 'Streetwear essentials and premium footwear for youth.'],
            ['name' => 'Medea Urban Store', 'city' => 'Médéa', 'desc' => 'Cozy fleece, hoodies, and trending sneakers.'],
            ['name' => 'Tiaret Trend Zone', 'city' => 'Tiaret', 'desc' => 'Authentic street apparel and limited sneaker drops.'],
            ['name' => 'Ouargla Desert Drip', 'city' => 'Ouargla', 'desc' => 'Exclusive streetwear fits and trendy footwear.'],
            ['name' => 'El Oued Oasis Kicks', 'city' => 'El Oued', 'desc' => 'Modern sneakers, graphic tees, and streetwear accessories.'],
            ['name' => 'Souk Ahras Drip Corner', 'city' => 'Souk Ahras', 'desc' => 'Quality urban clothing and top-selling street sneakers.'],
            ['name' => 'Ain Defla Street Closet', 'city' => 'Aïn Defla', 'desc' => 'Casual street style, cargos, and hype kicks.'],
            ['name' => 'Saida Urban Lab', 'city' => 'Saïda', 'desc' => 'Fresh streetwear collections and authentic footwear.'],
            ['name' => 'Laghouat Hype Station', 'city' => 'Laghouat', 'desc' => 'Popular streetwear brands, hoodies, and modern sneakers.'],
            ['name' => 'Ghardaia Trend House', 'city' => 'Ghardaïa', 'desc' => 'Authentic sneakers and curated fashion pieces.'],
            ['name' => 'Relizane Drip Supply', 'city' => 'Relizane', 'desc' => 'Youth streetwear apparel, oversized t-shirts, and kicks.'],
            ['name' => 'Ain Temouchent Sneaker Hub', 'city' => 'Aïn Témouchent', 'desc' => 'Limited edition sneakers and seaside casual streetwear.'],
            ['name' => 'Mila Street Selection', 'city' => 'Mila', 'desc' => 'Curated wardrobe essentials and trending sneakers.'],
            ['name' => 'Bouira Urban Goods', 'city' => 'Bouira', 'desc' => 'Streetwear staples, hoodies, and authentic sportswear.'],
            ['name' => 'Khenchela Drip Store', 'city' => 'Khenchela', 'desc' => 'Sportswear, skate fashion, and trending shoes.'],
            ['name' => 'Tebessa Borderline Kicks', 'city' => 'Tébessa', 'desc' => 'Imported original sneakers and high-demand streetwear.'],
            ['name' => 'Adrar Desert Outfits', 'city' => 'Adrar', 'desc' => 'Relaxed street fashion and lifestyle footwear.'],
            ['name' => 'Bechar Sahara Drip', 'city' => 'Béchar', 'desc' => 'Urban fashion hub featuring oversized hoodies and sneakers.'],
            ['name' => 'Bir El Djir Sneaker Space', 'city' => 'Oran', 'desc' => 'High-heat sneaker releases and premium streetwear apparel.'],
            ['name' => 'Cheraga Drip Room', 'city' => 'Algiers', 'desc' => 'Boutique street fashion, luxury apparel, and sneakers.'],
            ['name' => 'El Biar Streetwear Gallery', 'city' => 'Algiers', 'desc' => 'Authentic streetwear gallery with limited edition pieces.'],
            ['name' => 'Zeralda Coast Drip', 'city' => 'Algiers', 'desc' => 'Streetwear, beach lifestyle apparel, and trending sneakers.'],
            ['name' => 'Didouche Mourad Kicks', 'city' => 'Algiers', 'desc' => 'Downtown Algiers hub for sneakerheads and streetwear lovers.'],
        ];

        $users = User::all();
        $fallbackUserId = $users->first()?->id;

        $storeImages = [
            'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=500',
            'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=500',
            'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=500',
            'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?w=500',
            'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?w=500',
            'https://images.unsplash.com/photo-1486401899868-0e435ed85128?w=500',
            'https://images.unsplash.com/photo-1534452203293-494d7ddbf7e0?w=500',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=500',
        ];

        foreach ($storeTemplates as $index => $data) {
            $wilaya = Wilaya::where('en', 'like', '%' . $data['city'] . '%')
                ->orWhere('fr', 'like', '%' . $data['city'] . '%')
                ->first() ?: Wilaya::inRandomOrder()->first();

            $user = $users->isNotEmpty() ? $users->random() : null;
            $userId = $user?->id ?? $fallbackUserId;

            $phonePrefix = ['05', '06', '07'][array_rand(['05', '06', '07'])];
            $phone = $phonePrefix . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $imageUrl = $storeImages[$index % count($storeImages)] . '&auto=format&fit=crop&q=80';

            Store::firstOrCreate(
                ['name' => $data['name']],
                [
                    'user_id' => $userId,
                    'wilaya_id' => $wilaya?->id,
                    'phone_number' => $phone,
                    'description' => $data['desc'],
                    'image_url' => $imageUrl,
                    'password' => Hash::make('password'),
                    'password_plaintext' => 'password',
                    'store_status' => 'active',
                    'is_approved' => true,
                ]
            );
        }
    }
}
