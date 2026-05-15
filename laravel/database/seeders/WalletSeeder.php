<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            $walletId = DB::table('wallets')->insertGetId([
                'user_id' => $userId,
                'balance' => fake()->randomFloat(2, 0, 200000),
                'currency' => 'DZD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transactionsCount = fake()->numberBetween(0, 20);

            for ($i = 1; $i <= $transactionsCount; $i++) {
                $type = fake()->randomElement([
                    'deposit',
                    'withdraw',
                    'purchase',
                    'refund',
                    'drop_sale',
                    'prize_joining',
                ]);

                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $walletId,
                    'type' => $type,
                    'amount' => fake()->randomFloat(2, 100, 50000),

                    'related_type' => null,
                    'related_id' => null,

                    'description' => fake()->boolean(70)
                        ? ucfirst(str_replace('_', ' ', $type)).' transaction'
                        : null,

                    'status' => fake()->randomElement([
                        'pending',
                        'completed',
                        'completed',
                        'completed',
                        'failed',
                        'cancelled',
                    ]),

                    'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
