<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // 1. Create main balance wallet
            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'balance',
                ],
                [
                    'balance' => rand(10000, 75000),
                    'pending_balance' => rand(0, 5000),
                    'is_identity_verified' => true,
                    'status' => 'verified',
                    'currency' => 'DZD',
                ]
            );

            // 2. Add an earning deposit transaction (in)
            $earningTx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'direction' => 1,
                'type' => 'drops',
                'status' => 'completed',
                'amount' => 15000.00,
                'balance_before' => 0.00,
                'balance_after' => 15000.00,
                'title' => 'Drop Sales Commission',
                'reference' => '#DROP_' . strtoupper(substr(md5((string) $user->id), 0, 6)),
                'metadata' => ['note' => 'Creator earnings payout'],
            ]);

            // 3. Add a withdrawal transaction (out)
            $withdrawalTx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'direction' => 0,
                'type' => 'request-withdrawal',
                'status' => 'completed',
                'amount' => 5000.00,
                'balance_before' => 15000.00,
                'balance_after' => 10000.00,
                'title' => 'Withdrawal via BaridiMob',
                'reference' => '#WTH_' . strtoupper(substr(md5((string) ($user->id + 10)), 0, 6)),
                'metadata' => ['method' => 'baridimob'],
            ]);

            // 4. Create associated WithdrawalRequest
            WithdrawalRequest::create([
                'wallet_transaction_id' => $withdrawalTx->id,
                'user_id' => $user->id,
                'amount' => 5000.00,
                'method' => 0, // baridimob
                'status' => 4, // paid
                'transaction_id' => $withdrawalTx->id,
                'payment_details' => [
                    'rip' => '0079999900' . rand(10000000, 99999999),
                    'account_name' => $user->full_name ?? $user->username,
                ],
                'admin_note' => 'Processed automatically via BaridiMob API',
                'identity_checked_at' => now(),
                'approved_at' => now(),
                'paid_at' => now(),
            ]);
        }
    }
}
