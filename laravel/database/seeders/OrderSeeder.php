<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $wilayas = DB::table('wilayas')->get();
        $paymentMethodIds = DB::table('payment_methods')->pluck('id')->toArray();

        $products = DB::table('products')
            ->select('id', 'store_id', 'name', 'show_price')
            ->get();

        for ($i = 1; $i <= 600; $i++) {
            $product = $products->random();

            $sizeIds = DB::table('product_variants')
                ->where('product_id', $product->id)
                ->pluck('size_id')
                ->toArray();

            if (empty($sizeIds)) {
                continue;
            }

            $sizeId = fake()->randomElement($sizeIds);
            $wilaya = $wilayas->random();

            $quantity = fake()->numberBetween(1, 4);
            $unitPrice = $product->show_price ?: fake()->randomFloat(2, 1000, 30000);
            $subtotal = $unitPrice * $quantity;
            $deliveryFees = fake()->randomElement([0, 400, 500, 600, 700, 800, 1000]);
            $total = $subtotal + $deliveryFees;

            $status = fake()->randomElement([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
            ]);

            $hasClaimIssue = fake()->boolean(10);

            $orderId = DB::table('orders')->insertGetId([
                'wilaya_id' => $wilaya->id,
                'user_id' => fake()->boolean(85) ? fake()->randomElement($userIds) : null,
                'store_id' => $product->store_id,

                'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),

                'payment_method_id' => fake()->randomElement($paymentMethodIds),

                'full_name' => fake()->name(),
                'phone_number' => '05'.fake()->unique()->numerify('########'),

                'wilaya' => $wilaya->en ?? $wilaya->fr ?? $wilaya->ar ?? 'Wilaya',
                'baladiya' => fake()->city(),
                'home_address' => fake()->address(),

                'delivery_method' => fake()->randomElement([
                    Order::DELIVERY_METHOD_HOME,
                    Order::DELIVERY_METHOD_DESK,
                ]),

                'delivery_fees' => $deliveryFees,
                'subtotal' => $subtotal,
                'total' => $total,

                'status' => $status,
                'has_claim_issue' => $hasClaimIssue,
                'claim_issue' => $hasClaimIssue ? fake()->sentence() : null,

                'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                'updated_at' => now(),
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'size_id' => $sizeId,
                'product_name' => $product->name ?? 'Product '.$product->id,

                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $subtotal,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
