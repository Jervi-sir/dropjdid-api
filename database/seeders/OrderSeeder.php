<?php

namespace Database\Seeders;

use App\Models\Drop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Size;
use App\Models\Store;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $stores = Store::all();
        $products = Product::all();
        $drops = Drop::all();
        $sizes = Size::all();

        if ($stores->isEmpty() || $products->isEmpty()) {
            return;
        }

        $sampleOrders = [
            [
                'full_name' => 'Mohamed Brahimi',
                'phone_number' => '0551234567',
                'wilaya' => 'Algiers',
                'baladiya' => 'Bab Ezzouar',
                'home_address' => 'Cité 8 Mai 1945, Batiment C, Appt 12',
                'delivery_method' => 'home',
                'delivery_fees' => 600.00,
                'status' => 'delivered',
            ],
            [
                'full_name' => 'Amina Benali',
                'phone_number' => '0662345678',
                'wilaya' => 'Oran',
                'baladiya' => 'Bir El Djir',
                'home_address' => 'Résidence El Bahia, Bloc 4',
                'delivery_method' => 'home',
                'delivery_fees' => 700.00,
                'status' => 'shipped',
            ],
            [
                'full_name' => 'Yacine Mansouri',
                'phone_number' => '0773456789',
                'wilaya' => 'Constantine',
                'baladiya' => 'Ali Mendjeli',
                'home_address' => 'UV 05, Villa N° 23',
                'delivery_method' => 'desk',
                'delivery_fees' => 400.00,
                'status' => 'confirmed',
            ],
            [
                'full_name' => 'Rania Khelifi',
                'phone_number' => '0544567890',
                'wilaya' => 'Sétif',
                'baladiya' => 'El Eulma',
                'home_address' => 'Boulevard Dubai, Magasin N° 10',
                'delivery_method' => 'home',
                'delivery_fees' => 650.00,
                'status' => 'processing',
            ],
            [
                'full_name' => 'Khaled Zerrouki',
                'phone_number' => '0555678901',
                'wilaya' => 'Blida',
                'baladiya' => 'Ouled Yaich',
                'home_address' => 'Cité 1024 Logements, Bat A4',
                'delivery_method' => 'home',
                'delivery_fees' => 500.00,
                'status' => 'pending',
            ],
        ];

        foreach ($sampleOrders as $idx => $orderData) {
            $user = $users->isNotEmpty() ? $users->random() : null;
            $store = $stores->random();
            $wilaya = Wilaya::where('en', $orderData['wilaya'])->first() ?? Wilaya::inRandomOrder()->first();
            $orderStatus = OrderStatus::where('code', $orderData['status'])->first();

            $orderNumber = 'ORD-2026-' . strtoupper(Str::random(6));

            $order = Order::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'wilaya_id' => $wilaya?->id,
                    'user_id' => $user?->id,
                    'store_id' => $store->id,
                    'order_number' => $orderNumber,
                    'full_name' => $orderData['full_name'],
                    'phone_number' => $orderData['phone_number'],
                    'wilaya' => $orderData['wilaya'],
                    'baladiya' => $orderData['baladiya'],
                    'home_address' => $orderData['home_address'],
                    'delivery_method' => $orderData['delivery_method'],
                    'delivery_fees' => $orderData['delivery_fees'],
                    'subtotal' => 0.00,
                    'total' => 0.00,
                    'order_status_code' => $orderData['status'],
                    'has_claim_issue' => false,
                ]
            );

            // Create 1-3 Order Items
            $itemCount = rand(1, 3);
            $selectedProducts = $products->random(min($itemCount, $products->count()));
            $subtotal = 0.00;

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 2);
                $unitPrice = (float) ($product->price_shown ?? 15000.00);
                $totalPrice = $unitPrice * $qty;
                $subtotal += $totalPrice;

                $size = $sizes->where('category_id', $product->category_id)->first() ?? $sizes->first();
                $drop = $drops->isNotEmpty() ? $drops->random() : null;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'drop_id' => $drop?->id,
                    'size_id' => $size?->id ?? 1,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Update order financial sums
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $orderData['delivery_fees'],
            ]);
        }
    }
}
