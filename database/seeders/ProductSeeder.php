<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\ProductVariant;
use App\Models\Quality;
use App\Models\Size;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsCatalog = [
            // SNEAKERS & SHOES (shoes)
            ['name' => 'Nike Dunk Low Retro White Black (Panda)', 'cat' => 'shoes', 'price' => 21000, 'img' => ['https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600', 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=600'], 'desc' => 'Classic retro basketball sneaker with black and white leather overlays and lightweight cushioning.'],
            ['name' => 'Adidas Originals Samba OG White Black Gum', 'cat' => 'shoes', 'price' => 19500, 'img' => ['https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600'], 'desc' => 'Iconic low-profile silhouette with leather upper, suede T-toe cap, and gum rubber outsole.'],
            ['name' => 'Air Jordan 1 Retro High OG Chicago Lost & Found', 'cat' => 'shoes', 'price' => 38000, 'img' => ['https://images.unsplash.com/photo-1552346154-21d32810aba3?w=600'], 'desc' => 'Legendary high-top sneaker in classic Chicago colorway with cracked vintage leather build.'],
            ['name' => 'Air Jordan 4 Retro Military Black', 'cat' => 'shoes', 'price' => 44000, 'img' => ['https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=600'], 'desc' => 'Premium smooth white leather, neutral grey suede overlays, and black TPU accents.'],
            ['name' => 'Air Jordan 4 Retro Black Cat', 'cat' => 'shoes', 'price' => 49000, 'img' => ['https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=600'], 'desc' => 'All-black premium nubuck upper, matte black midsole, and iconic Jumpman tongue branding.'],
            ['name' => 'Travis Scott x Air Jordan 1 Low Reverse Mocha', 'cat' => 'shoes', 'price' => 55000, 'img' => ['https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600'], 'desc' => 'Signature reverse Swoosh, premium mocha suede and sail leather overlays.'],
            ['name' => 'New Balance 550 White Grey', 'cat' => 'shoes', 'price' => 18500, 'img' => ['https://images.unsplash.com/photo-1539185441755-769473a23570?w=600'], 'desc' => 'Vintage 80s basketball tribute with smooth white leather and breathable mesh collar.'],
            ['name' => 'New Balance 2002R Protection Pack Rain Cloud', 'cat' => 'shoes', 'price' => 28000, 'img' => ['https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600'], 'desc' => 'Deconstructed raw-edge suede overlays with N-ERGY shock absorption cushioning.'],
            ['name' => 'New Balance 9060 Triple Black', 'cat' => 'shoes', 'price' => 29500, 'img' => ['https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600'], 'desc' => 'Futuristic exaggerated silhouette with dual-density ABZORB and SBS midsole.'],
            ['name' => 'Adidas Campus 00s Core Black White', 'cat' => 'shoes', 'price' => 17000, 'img' => ['https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600'], 'desc' => 'Chunky Y2K skate aesthetic with plush suede upper and thick fat laces.'],
            ['name' => 'Nike Air Force 1 \'07 Triple White', 'cat' => 'shoes', 'price' => 16500, 'img' => ['https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600'], 'desc' => 'The timeless crisp white leather classic with encapsulated Air-Sole cushioning.'],
            ['name' => 'Nike Air Max Plus TN Triple Black', 'cat' => 'shoes', 'price' => 27500, 'img' => ['https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=600'], 'desc' => 'Tuned Air technology, breathable mesh upper, and distinctive TPU flame rib cage.'],
            ['name' => 'Asics Gel-Kayano 14 Silver Cream', 'cat' => 'shoes', 'price' => 24000, 'img' => ['https://images.unsplash.com/photo-1539185441755-769473a23570?w=600'], 'desc' => 'Late 2000s technical runner design with metallic leather overlays and GEL cushioning.'],
            ['name' => 'Salomon XT-6 Black Phantom', 'cat' => 'shoes', 'price' => 31000, 'img' => ['https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=600'], 'desc' => 'Gorpcore trail sneaker with Agile Chassis System and Quicklace drawcord closure.'],
            ['name' => 'Nike Zoom Vomero 5 Photon Dust', 'cat' => 'shoes', 'price' => 26000, 'img' => ['https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600'], 'desc' => 'Complex layered runner featuring mesh ventilation ports and dual Zoom Air units.'],
            ['name' => 'Adidas Yeezy Boost 350 V2 Onyx', 'cat' => 'shoes', 'price' => 36000, 'img' => ['https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=600'], 'desc' => 'Re-engineered Primeknit upper with full-length Boost midsole in dark onyx tone.'],
            ['name' => 'Nike SB Dunk Low Pro White Gum', 'cat' => 'shoes', 'price' => 22500, 'img' => ['https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600'], 'desc' => 'Skate-ready padded tongue, zoom air insole, and sticky gum rubber outsole.'],
            ['name' => 'Puma Palermo Special White Green', 'cat' => 'shoes', 'price' => 15500, 'img' => ['https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600'], 'desc' => 'Terrace culture throwback sneaker with signature gold foil Palermo tag on upper.'],
            ['name' => 'Bape Sta Low White Black', 'cat' => 'shoes', 'price' => 34000, 'img' => ['https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600'], 'desc' => 'Glossy patent leather upper with contrasting Bape Star logo and Ape head charm.'],
            ['name' => 'Alexander McQueen Oversized Sneaker White Black', 'cat' => 'shoes', 'price' => 45000, 'img' => ['https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600'], 'desc' => 'Smooth calfskin leather luxury sneaker with exaggerated chunky platform rubber sole.'],

            // HOODIES & SWEATSHIRTS (streetwear / clothing)
            ['name' => 'Essentials Fear of God Heavyweight Hoodie Desert Taupe', 'cat' => 'streetwear', 'price' => 16500, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600'], 'desc' => 'Boxy oversized fleece hoodie featuring rubberized logo on chest and back.'],
            ['name' => 'Supreme Box Logo Heavyweight Crewneck Heather Grey', 'cat' => 'streetwear', 'price' => 24000, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Cross-grain cotton fleece crewneck with embroidered red box logo and side rib gussets.'],
            ['name' => 'Stussy 8-Ball Heavyweight Fleece Hoodie Black', 'cat' => 'streetwear', 'price' => 17500, 'img' => ['https://images.unsplash.com/photo-1509967419530-da38b4704bc6?w=600'], 'desc' => 'Relaxed fit pullover hoodie with iconic screenprinted 8-ball graphic on back and chest.'],
            ['name' => 'Trapstar Hyperdrive Chenille Decoded Hoodie Black/Blue', 'cat' => 'streetwear', 'price' => 21000, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600'], 'desc' => 'Signature Chenille towel embroidery on chest with custom metal zip puller.'],
            ['name' => 'Corteiz Guerillaz OG Pullover Hoodie Grey', 'cat' => 'streetwear', 'price' => 19000, 'img' => ['https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600'], 'desc' => 'Heavyweight 450 GSM cotton fleece with iconic Alcatraz screenprinted graphic.'],
            ['name' => 'Nike Tech Fleece Full-Zip Windrunner Black', 'cat' => 'sportswear', 'price' => 18000, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600'], 'desc' => 'Thermal lightweight fleece with chevron chest design and taped zippered sleeve pocket.'],
            ['name' => 'Carhartt WIP Chase Heavyweight Sweatshirt Ash Heather', 'cat' => 'clothing', 'price' => 14000, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Classic raglan sleeve crewneck with subtle gold Carhartt \'C\' embroidery on sleeve cuff.'],
            ['name' => 'Palm Angels Classic Curved Logo Track Hoodie Black/White', 'cat' => 'streetwear', 'price' => 26000, 'img' => ['https://images.unsplash.com/photo-1509967419530-da38b4704bc6?w=600'], 'desc' => 'Gothic font logo print across back collar and shoulders with drawcord hood.'],
            ['name' => 'Represent Owners Club Oversized Hoodie Vintage Black', 'cat' => 'streetwear', 'price' => 19500, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600'], 'desc' => 'Custom heavyweight loopback cotton with metal cobrax popper on hood.'],
            ['name' => 'Bape Shark Full Zip Double Hoodie Camo Green', 'cat' => 'streetwear', 'price' => 32000, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600'], 'desc' => 'Full zip through hood with classic Bape camouflage and felt shark mouth graphic.'],
            ['name' => 'Ami Paris De Coeur Heavy Knit Wool Sweater Black', 'cat' => 'clothing', 'price' => 29000, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Extra-fine virgin wool crewneck sweater with oversized red Ami De Coeur intarsia.'],
            ['name' => 'Drew House Mascot Skate Hoodie Golden Yellow', 'cat' => 'streetwear', 'price' => 15000, 'img' => ['https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600'], 'desc' => 'Vibrant heavyweight fleece featuring smiley mascot screenprint on front.'],
            ['name' => 'Rhude Moonlight Scenery Vintage Pullover Hoodie', 'cat' => 'streetwear', 'price' => 22000, 'img' => ['https://images.unsplash.com/photo-1509967419530-da38b4704bc6?w=600'], 'desc' => 'Sun-faded vintage washed cotton fleece with sunburst back graphic.'],
            ['name' => 'Off-White Caravaggio Arrows Oversized Hoodie', 'cat' => 'streetwear', 'price' => 31000, 'img' => ['https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600'], 'desc' => 'Masterpiece oil painting graphic inside iconic diagonal arrow motif.'],
            ['name' => 'Lacoste Sport Zip Collar Retro Sweatshirt Navy', 'cat' => 'sportswear', 'price' => 17000, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Heritage French tennis sweater with quarter-zip stand collar and green crocodile.'],

            // T-SHIRTS & TOPS (clothing / streetwear)
            ['name' => 'Stussy Basic Logo Pigment Dyed Tee Washed Black', 'cat' => 'clothing', 'price' => 7500, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => '100% pigment-dyed cotton jersey with signature Stussy script logo on chest.'],
            ['name' => 'Corteiz Alcatraz Heavy Cotton Tee White', 'cat' => 'streetwear', 'price' => 8500, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => '240 GSM pre-shrunk cotton tee featuring bold Alcatraz island logo screenprinted on front.'],
            ['name' => 'Supreme Motion Logo Lightweight Tee Black', 'cat' => 'streetwear', 'price' => 9500, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Classic Goodfellas inspired blurred motion logo printed across chest.'],
            ['name' => 'Gallery Dept. Souvenir French Vintage Tee Cream', 'cat' => 'streetwear', 'price' => 12000, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => 'Distressed vintage aesthetic with hand-painted splatters and washed faded feel.'],
            ['name' => 'Travis Scott Utopia Circus Maximus Tour Tee', 'cat' => 'streetwear', 'price' => 10500, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Official Cactus Jack oversized tour merchandise with puff-print typography.'],
            ['name' => 'Represent Vintage Initial Boxy Tee Off-Black', 'cat' => 'streetwear', 'price' => 9000, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => 'Relaxed boxy silhouette crafted from premium pre-shrunk medium-weight cotton.'],
            ['name' => 'Ralph Lauren Custom Slim Fit Polo Navy Blue', 'cat' => 'clothing', 'price' => 13500, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => 'Iconic breathable cotton mesh polo with signature pony embroidery.'],
            ['name' => 'Carhartt WIP Pocket Heavy Tee Black', 'cat' => 'clothing', 'price' => 6800, 'img' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600'], 'desc' => 'Durable 220 GSM single jersey with single chest pocket and square label.'],
            ['name' => 'Nike Sportswear Club Graphic Tee White', 'cat' => 'sportswear', 'price' => 5200, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => 'Soft everyday lightweight cotton fabric with Futura Nike logo screenprint.'],
            ['name' => 'Casablanca Casa Way Tennis Club Graphic Tee', 'cat' => 'streetwear', 'price' => 16000, 'img' => ['https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600'], 'desc' => 'Luxury organic cotton tee featuring vibrant resort tennis artwork.'],

            // PANTS, CARGOS & DENIM (clothing / streetwear)
            ['name' => 'Corteiz Guerillaz Cargo Pants Olive Green', 'cat' => 'streetwear', 'price' => 17500, 'img' => ['https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600'], 'desc' => 'Heavy duty ripstop cargo trousers with six utility pockets and cinchable ankle cuffs.'],
            ['name' => 'Zara Baggy Cargo Parachute Trousers Charcoal', 'cat' => 'clothing', 'price' => 7800, 'img' => ['https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600'], 'desc' => 'Wide-leg relaxed fit cargo trousers with adjustable drawstring waistband.'],
            ['name' => 'Carhartt WIP Single Knee Pant Hamilton Brown', 'cat' => 'clothing', 'price' => 16000, 'img' => ['https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600'], 'desc' => 'Rugged 12oz Dearborn canvas pants with triple-stitched bar-tacks and hammer loop.'],
            ['name' => 'Polar Skate Co. Big Boy Jeans Light Blue', 'cat' => 'streetwear', 'price' => 18000, 'img' => ['https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600'], 'desc' => 'Ultra-baggy skate silhouette with custom brass button and Big Boy coin pocket embroidery.'],
            ['name' => 'Nike Tech Fleece Jogger Pants Dark Grey Heather', 'cat' => 'sportswear', 'price' => 14500, 'img' => ['https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600'], 'desc' => 'Slim tapered track pants with large bonded zippered pocket and elastic waistband.'],
            ['name' => 'Stussy Big Ol Jeans Washed Indigo Denim', 'cat' => 'streetwear', 'price' => 17000, 'img' => ['https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600'], 'desc' => '5-pocket wide leg denim pants with classic leather debossed belt patch.'],
            ['name' => 'Essentials Relaxed Fleece Sweatpants Light Oatmeal', 'cat' => 'streetwear', 'price' => 13500, 'img' => ['https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600'], 'desc' => 'Cozy brushed-back fleece bottoms with elongated drawstring and rubberized logo.'],
            ['name' => 'Trapstar Iridescent Shooters Track Bottoms Black', 'cat' => 'streetwear', 'price' => 15500, 'img' => ['https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600'], 'desc' => 'Sleek track trousers with reflective piping and custom zip ankle openings.'],
            ['name' => 'Levi\'s 501 Original Fit Jeans Stonewash Blue', 'cat' => 'clothing', 'price' => 12500, 'img' => ['https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600'], 'desc' => 'The iconic straight leg fit with button fly and signature copper rivets.'],
            ['name' => 'Represent 247 Mission Pant Jet Black', 'cat' => 'sportswear', 'price' => 19000, 'img' => ['https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600'], 'desc' => '4-way stretch water-repellent performance pants designed for all-day comfort.'],

            // JACKETS & OUTERWEAR (streetwear / clothing)
            ['name' => 'The North Face 1996 Retro Nuptse Down Jacket Black', 'cat' => 'clothing', 'price' => 38000, 'img' => ['https://images.unsplash.com/photo-1544441893-675973e31985?w=600'], 'desc' => '700-fill goose down insulation with durable water-repellent ripstop shell.'],
            ['name' => 'Arc\'teryx Beta LT Waterproof Jacket Black Sapphire', 'cat' => 'clothing', 'price' => 48000, 'img' => ['https://images.unsplash.com/photo-1548883354-7622d03aca27?w=600'], 'desc' => 'GORE-TEX 3L waterproof breathable shell with StormHood and watertight zippers.'],
            ['name' => 'Carhartt WIP Detroit Blanket-Lined Jacket Brown', 'cat' => 'clothing', 'price' => 26000, 'img' => ['https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600'], 'desc' => 'Rugged canvas trucker jacket with corduroy collar and warm striped blanket lining.'],
            ['name' => 'Trapstar Hyperdrive Technical Puffer Jacket Shiny Black', 'cat' => 'streetwear', 'price' => 33000, 'img' => ['https://images.unsplash.com/photo-1544441893-675973e31985?w=600'], 'desc' => 'Glossy insulated down puffer featuring detachable hood and tonal chest branding.'],
            ['name' => 'Supreme Faux Fur Varsity Bomber Jacket Navy/White', 'cat' => 'streetwear', 'price' => 39000, 'img' => ['https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600'], 'desc' => 'Plush faux fur body with quilted satin lining and striped ribbed collar.'],
            ['name' => 'Nike Sportswear Windrunner Heritage Hooded Jacket', 'cat' => 'sportswear', 'price' => 14000, 'img' => ['https://images.unsplash.com/photo-1548883354-7622d03aca27?w=600'], 'desc' => 'Lightweight woven windbreaker with breathable mesh lining and 26-degree chevron.'],
            ['name' => 'Moncler Maya Glossy Short Down Jacket Black', 'cat' => 'clothing', 'price' => 65000, 'img' => ['https://images.unsplash.com/photo-1544441893-675973e31985?w=600'], 'desc' => 'Lacquered nylon finish with direct down injection and felt Moncler sleeve badge.'],
            ['name' => 'Stussy Reversible Quilted Work Vest Washed Black', 'cat' => 'streetwear', 'price' => 16500, 'img' => ['https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600'], 'desc' => 'Heavy duty canvas on one side, onion-quilted ripstop on reverse.'],

            // ACCESSORIES & HEADWEAR (accessories)
            ['name' => 'Stussy Low Pro Cap Pigment Print Black', 'cat' => 'accessories', 'price' => 5800, 'img' => ['https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600'], 'desc' => '6-panel unstructured dad hat with antique brass buckle strapback.'],
            ['name' => 'Supreme New Era Box Logo Beanie Heather Grey', 'cat' => 'accessories', 'price' => 7200, 'img' => ['https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600'], 'desc' => 'Ribbed acrylic knit beanie with embroidered Box Logo on front fold.'],
            ['name' => 'Trapstar Chenille Decoded Crossbody Bag Black', 'cat' => 'accessories', 'price' => 9800, 'img' => ['https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600'], 'desc' => 'Compact messenger body bag with buckle strap and branded rubber badge.'],
            ['name' => 'Carhartt WIP Acrylic Watch Beanie Hamilton Brown', 'cat' => 'accessories', 'price' => 4500, 'img' => ['https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600'], 'desc' => 'Warm 7-gauge stretchable rib-knit hat with woven square label.'],
            ['name' => 'Prada Re-Nylon Triangle Logo Bucket Hat Black', 'cat' => 'accessories', 'price' => 24000, 'img' => ['https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600'], 'desc' => 'Regenerated nylon gabardine with iconic enameled metal triangle logo.'],
            ['name' => 'Corteiz Alcatraz Thermal Balaclava Shiesty Black', 'cat' => 'accessories', 'price' => 6000, 'img' => ['https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600'], 'desc' => 'Full-face ski mask with Alcatraz logo print and breathable eye opening.'],
            ['name' => 'Oakley Radar EV Path Sunglasses Matte Black / Prizm', 'cat' => 'accessories', 'price' => 19500, 'img' => ['https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600'], 'desc' => 'High-performance eyewear with Plutonite UV lens and Unobtainium earsocks.'],
            ['name' => 'Nike Everyday Plus Cushioned Tie-Dye Crew Socks (3-Pack)', 'cat' => 'accessories', 'price' => 3200, 'img' => ['https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=600'], 'desc' => 'Dri-FIT moisture wicking socks with arch band support in vivid tie-dye tones.'],
        ];

        // Brands and items modifiers to programmatically generate 200 rich unique products
        $brands = [
            'Nike', 'Adidas Originals', 'Air Jordan', 'New Balance', 'Stussy',
            'Supreme', 'Essentials Fear of God', 'Trapstar London', 'Corteiz RTW',
            'Carhartt WIP', 'The North Face', 'Arc\'teryx', 'Palm Angels', 'Represent Clo',
            'Bape A Bathing Ape', 'Off-White c/o Virgil', 'Rhude Design', 'Ami Paris',
            'Salomon S/Lab', 'Asics Sportstyle', 'Lacoste', 'Puma Select', 'Stone Island',
            'Palace Skateboards', 'Kith NYC', 'Undercover Jun Takahashi', 'Human Made',
            'Casablanca Paris', 'Gallery Dept', 'Zara Man Street'
        ];

        $categories = Category::all()->keyBy('code');
        $qualities = Quality::all();
        $genders = Gender::all();
        $stores = Store::all();
        $keywords = Keyword::all();

        $curatedCount = count($productsCatalog);
        $totalTarget = 200;

        // Base image pools by category
        $shoesImages = [
            'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600',
            'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=600',
            'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=600',
            'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600',
            'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600',
            'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600',
            'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600',
            'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=600',
            'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600',
            'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=600',
        ];

        $apparelImages = [
            'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600',
            'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600',
            'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600',
            'https://images.unsplash.com/photo-1509967419530-da38b4704bc6?w=600',
            'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600',
            'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600',
            'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600',
            'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600',
            'https://images.unsplash.com/photo-1544441893-675973e31985?w=600',
            'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=600',
            'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600',
        ];

        $accessoryImages = [
            'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600',
            'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600',
            'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600',
            'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600',
            'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=600',
        ];

        $productTypeTemplates = [
            ['title' => 'Oversized Heavyweight Washed Tee', 'cat' => 'streetwear', 'base_price' => 7000],
            ['title' => 'Vintage Distressed Boxy Hoodie', 'cat' => 'streetwear', 'base_price' => 15000],
            ['title' => 'Wide-Leg Utility Cargo Pants', 'cat' => 'clothing', 'base_price' => 12000],
            ['title' => 'Heritage Track Full-Zip Jacket', 'cat' => 'sportswear', 'base_price' => 16500],
            ['title' => 'Relaxed Fit Graphic Pullover', 'cat' => 'streetwear', 'base_price' => 14000],
            ['title' => 'Classic Cotton Twill Chino Trousers', 'cat' => 'clothing', 'base_price' => 11000],
            ['title' => 'Technical Puffer Down Gilet', 'cat' => 'clothing', 'base_price' => 18000],
            ['title' => 'Retro Skate Suede Sneakers', 'cat' => 'shoes', 'base_price' => 21000],
            ['title' => 'Chunky Platform Street Trainers', 'cat' => 'shoes', 'base_price' => 24000],
            ['title' => 'Raw Selvedge Denim Baggy Jeans', 'cat' => 'clothing', 'base_price' => 16000],
            ['title' => 'Embroidered Strapback Dad Cap', 'cat' => 'accessories', 'base_price' => 5000],
            ['title' => 'Ripstop Modular Messenger Bag', 'cat' => 'accessories', 'base_price' => 8500],
            ['title' => 'Heavy Knit Ribbed Winter Beanie', 'cat' => 'accessories', 'base_price' => 4500],
            ['title' => 'Windproof Technical Mountain Shell', 'cat' => 'sportswear', 'base_price' => 28000],
            ['title' => 'French Terry Everyday Sweatshorts', 'cat' => 'clothing', 'base_price' => 7500],
            ['title' => 'Retro Low-Top Basketball Kicks', 'cat' => 'shoes', 'base_price' => 22000],
        ];

        $colorVariations = [
            'Triple Black', 'Vintage White', 'Sage Green', 'Washed Grey',
            'Midnight Navy', 'Desert Sand', 'Oatmeal Heather', 'Olive Drab',
            'Charcoal Black', 'Royal Blue', 'Mocha Brown', 'Crimson Red'
        ];

        // Build list up to 200
        $finalItems = $productsCatalog;

        $index = 1;
        while (count($finalItems) < $totalTarget) {
            $brand = $brands[($index * 7) % count($brands)];
            $type = $productTypeTemplates[$index % count($productTypeTemplates)];
            $color = $colorVariations[($index * 5) % count($colorVariations)];

            $name = "{$brand} {$type['title']} ({$color})";
            $catCode = $type['cat'];
            $price = $type['base_price'] + (($index % 7) * 1200);

            if ($catCode === 'shoes') {
                $imgs = [
                    $shoesImages[$index % count($shoesImages)],
                    $shoesImages[($index + 3) % count($shoesImages)],
                ];
            } elseif ($catCode === 'accessories') {
                $imgs = [
                    $accessoryImages[$index % count($accessoryImages)],
                ];
            } else {
                $imgs = [
                    $apparelImages[$index % count($apparelImages)],
                    $apparelImages[($index + 4) % count($apparelImages)],
                ];
            }

            $finalItems[] = [
                'name' => $name,
                'cat' => $catCode,
                'price' => $price,
                'img' => $imgs,
                'desc' => "Premium {$color} edition {$type['title']} by {$brand}. Engineered for premium comfort, streetwear silhouette, and everyday durability.",
            ];

            $index++;
        }

        foreach ($finalItems as $data) {
            $category = $categories[$data['cat']] ?? Category::inRandomOrder()->first();
            $gender = $genders->isNotEmpty() ? $genders->random() : Gender::inRandomOrder()->first();
            $quality = $qualities->isNotEmpty() ? $qualities->random() : Quality::inRandomOrder()->first();
            $store = $stores->isNotEmpty() ? $stores->random() : Store::inRandomOrder()->first();

            $priceShown = (float) $data['price'];
            $priceOriginal = round($priceShown * 1.25, -2);
            $priceStore = round($priceShown * 0.80, -2);

            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                [
                    'store_id' => $store?->id,
                    'category_id' => $category?->id,
                    'gender_id' => $gender?->id,
                    'quality_id' => $quality?->id,
                    'description' => $data['desc'],
                    'price_original' => $priceOriginal,
                    'price_shown' => $priceShown,
                    'price_store' => $priceStore,
                    'product_status' => 'published',
                    'refreshed_at' => now(),
                ]
            );

            // 1. Create Images
            foreach ($data['img'] as $idx => $imgUrl) {
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_url' => $imgUrl,
                    ],
                    [
                        'product_id' => $product->id,
                        'image_url' => $imgUrl,
                        'sort_order' => $idx,
                        'is_main' => $idx === 0,
                    ]
                );
            }

            // 2. Create Variants with matching category sizes
            $sizes = Size::where('category_id', $category?->id)->get();
            if ($sizes->isEmpty()) {
                $sizes = Size::inRandomOrder()->limit(3)->get();
            }

            foreach ($sizes as $size) {
                ProductVariant::firstOrCreate([
                    'product_id' => $product->id,
                    'size_id' => $size->id,
                ], [
                    'quantity' => rand(8, 45),
                ]);
            }

            // 3. Attach Keywords
            if ($keywords->isNotEmpty()) {
                $randomKws = $keywords->random(min(rand(2, 4), $keywords->count()));
                foreach ($randomKws as $kw) {
                    ProductKeyword::firstOrCreate([
                        'product_id' => $product->id,
                        'keyword_id' => $kw->id,
                        'label_id' => $kw->label_id,
                    ]);
                }
            }
        }
    }
}
