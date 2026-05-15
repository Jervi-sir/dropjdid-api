<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            User::query()->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $balanceWallet = Wallet::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'type' => 'balance',
                        ],
                        [
                            'balance' => 0,
                            'pending_balance' => 0,
                            'currency' => 'DZD',
                        ]
                    );

                    $refundWallet = Wallet::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'type' => 'refund',
                        ],
                        [
                            'balance' => 0,
                            'pending_balance' => 0,
                            'currency' => 'DZD',
                        ]
                    );

                    if ($balanceWallet->transactions()->doesntExist()) {
                        $this->createTransaction(
                            wallet: $balanceWallet,
                            user: $user,
                            direction: 'in',
                            type: 'drops',
                            amount: 1200,
                            title: 'Drop earning',
                            reference: '#Colden_men_visiting_forest'
                        );

                        $this->createTransaction(
                            wallet: $balanceWallet,
                            user: $user,
                            direction: 'in',
                            type: 'bonus',
                            amount: 500,
                            title: 'Welcome bonus',
                            reference: '#WELCOME_BONUS'
                        );
                    }

                    if ($refundWallet->transactions()->doesntExist()) {
                        $this->createTransaction(
                            wallet: $refundWallet,
                            user: $user,
                            direction: 'in',
                            type: 'refund',
                            amount: 2000,
                            title: 'Order refund',
                            reference: '#ORDER_REFUND_001'
                        );
                    }
                }
            });
        });

    }

    private function createTransaction(
        Wallet $wallet,
        User $user,
        string $direction,
        string $type,
        float|int $amount,
        string $title,
        string $reference,
        string $status = 'completed',
    ): void {
        $balanceBefore = (float) $wallet->balance;

        $balanceAfter = $direction === 'in'
            ? $balanceBefore + $amount
            : $balanceBefore - $amount;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'direction' => $direction,
            'type' => $type,
            'status' => $status,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'title' => $title,
            'reference' => $reference,
            'metadata' => [
                'seeded' => true,
            ],
        ]);

        $wallet->update([
            'balance' => $balanceAfter,
        ]);
    }
}
