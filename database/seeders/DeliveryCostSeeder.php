<?php

namespace Database\Seeders;

use App\Models\DeliveryCompany;
use App\Models\Store;
use App\Models\StoreToDeliveryCost;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class DeliveryCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Delivery Companies
        $companies = [
            [
                'code' => 'yalidine',
                'name' => 'Yalidine Express',
                'logo_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=200',
                'phone' => '0982400000',
                'website' => 'https://yalidine.app',
                'is_active' => true,
            ],
            [
                'code' => 'swift_express',
                'name' => 'Swift Express',
                'logo_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=200',
                'phone' => '0770123456',
                'website' => 'https://swiftexpress.dz',
                'is_active' => true,
            ],
            [
                'code' => 'zr_express',
                'name' => 'ZR Express',
                'logo_url' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=200',
                'phone' => '0550987654',
                'website' => 'https://zrexpress.dz',
                'is_active' => true,
            ],
            [
                'code' => 'ecotrack',
                'name' => 'EcoTrack Express',
                'logo_url' => null,
                'phone' => '0560112233',
                'website' => 'https://ecotrack.dz',
                'is_active' => true,
            ],
        ];

        $seededCompanies = [];
        foreach ($companies as $comp) {
            $seededCompanies[$comp['code']] = DeliveryCompany::firstOrCreate(['code' => $comp['code']], $comp);
        }

        // 2. Seed Default Delivery Costs for each store & wilaya
        $stores = Store::all();
        $wilayas = Wilaya::all();
        $primaryCompany = $seededCompanies['swift_express'] ?? DeliveryCompany::first();

        // Standard Algerian delivery pricing baseline by zone
        $algiersNearby = ['16', '09', '35', '42']; // Algiers, Blida, Boumerdes, Tipaza
        $northCentral = ['02', '10', '15', '26', '44']; 
        $eastWest = ['31', '25', '19', '23', '13', '27', '21', '06', '18', '22', '24', '28', '29', '41', '43', '46', '48'];
        $southWilayas = ['01', '03', '07', '08', '11', '17', '30', '32', '33', '37', '39', '40', '45', '47', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58'];

        foreach ($stores as $store) {
            foreach ($wilayas as $wilaya) {
                $wilayaNum = (string) $wilaya->number;

                if (in_array($wilayaNum, $algiersNearby, true)) {
                    $costHome = 400.00;
                    $costStopdesk = 250.00;
                } elseif (in_array($wilayaNum, $northCentral, true)) {
                    $costHome = 500.00;
                    $costStopdesk = 300.00;
                } elseif (in_array($wilayaNum, $eastWest, true)) {
                    $costHome = 650.00;
                    $costStopdesk = 400.00;
                } elseif (in_array($wilayaNum, $southWilayas, true)) {
                    $costHome = 950.00;
                    $costStopdesk = 600.00;
                } else {
                    $costHome = 600.00;
                    $costStopdesk = 350.00;
                }

                StoreToDeliveryCost::firstOrCreate([
                    'store_id' => $store->id,
                    'wilaya_id' => $wilaya->id,
                    'delivery_company_code' => $primaryCompany?->code ?? 'swift_express',
                ], [
                    'store_id' => $store->id,
                    'delivery_company_id' => $primaryCompany?->id,
                    'delivery_company_code' => $primaryCompany?->code ?? 'swift_express',
                    'wilaya_id' => $wilaya->id,
                    'wilaya_name' => $wilaya->fr ?? $wilaya->en ?? $wilaya->code,
                    'cost_domicile' => $costHome,
                    'cost_stopdesk' => $costStopdesk,
                    'cost_cancel' => 200.00,
                    'is_active' => true,
                ]);
            }
        }
    }
}
