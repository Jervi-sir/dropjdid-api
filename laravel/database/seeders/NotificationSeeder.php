<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'sales', 'en' => 'Sales', 'fr' => 'Ventes', 'ar' => 'المبيعات'],
            ['code' => 'withdraw', 'en' => 'Withdraw', 'fr' => 'Retrait', 'ar' => 'السحب'],
            ['code' => 'tracking_order', 'en' => 'Tracking Order', 'fr' => 'Suivi de commande', 'ar' => 'تتبع الطلب'],
            ['code' => 'friend_request', 'en' => 'Friend Request', 'fr' => "Demande d'ami", 'ar' => 'طلب صداقة'],
            ['code' => 'followers', 'en' => 'Followers', 'fr' => 'Abonnés', 'ar' => 'المتابعون'],
        ];

        foreach ($types as $type) {
            DB::table('notification_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $typeIds = DB::table('notification_types')->pluck('id', 'code');
        $userIds = DB::table('users')->pluck('id')->toArray();

        $orderIds = DB::table('orders')->pluck('id')->toArray();
        $walletTransactionIds = DB::table('wallet_transactions')->pluck('id')->toArray();
        $friendshipIds = DB::table('friendships')->pluck('id')->toArray();
        $followerIds = DB::table('creator_followers')->pluck('id')->toArray();

        for ($i = 1; $i <= 600; $i++) {
            $code = fake()->randomElement(array_keys($typesByCode = $types));
            $code = $types[array_rand($types)]['code'];

            [$notifiableType, $notifiableId] = $this->pickNotifiable(
                $code,
                $orderIds,
                $walletTransactionIds,
                $friendshipIds,
                $followerIds
            );

            DB::table('notifications')->insert([
                'notification_type_id' => $typeIds[$code],
                'user_id' => fake()->randomElement($userIds),

                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,

                'data' => json_encode($this->makeData($code), JSON_UNESCAPED_UNICODE),

                'read_at' => fake()->boolean(55)
                    ? now()->subDays(fake()->numberBetween(0, 30))
                    : null,

                'created_at' => now()->subDays(fake()->numberBetween(0, 60)),
                'updated_at' => now(),
            ]);
        }
    }

    private function pickNotifiable(
        string $code,
        array $orderIds,
        array $walletTransactionIds,
        array $friendshipIds,
        array $followerIds
    ): array {
        if (($code === 'sales' || $code === 'tracking_order') && !empty($orderIds)) {
            return [
                'App\\Models\\Order',
                fake()->randomElement($orderIds),
            ];
        }

        if ($code === 'withdraw' && !empty($walletTransactionIds)) {
            return [
                'App\\Models\\WalletTransaction',
                fake()->randomElement($walletTransactionIds),
            ];
        }

        if ($code === 'friend_request' && !empty($friendshipIds)) {
            return [
                'App\\Models\\Friendship',
                fake()->randomElement($friendshipIds),
            ];
        }

        if ($code === 'followers' && !empty($followerIds)) {
            return [
                'App\\Models\\CreatorFollower',
                fake()->randomElement($followerIds),
            ];
        }

        return [
            'App\\Models\\User',
            fake()->randomElement(DB::table('users')->pluck('id')->toArray()),
        ];
    }

    private function makeData(string $code): array
    {
        return match ($code) {
            'sales' => [
                'title' => 'New sale',
                'body' => 'You received a new product sale.',
            ],
            'withdraw' => [
                'title' => 'Withdraw update',
                'body' => 'Your withdrawal request has been updated.',
            ],
            'tracking_order' => [
                'title' => 'Order tracking',
                'body' => 'Your order status has changed.',
            ],
            'friend_request' => [
                'title' => 'Friend request',
                'body' => 'Someone sent you a friend request.',
            ],
            'followers' => [
                'title' => 'New follower',
                'body' => 'Someone started following you.',
            ],
            default => [
                'title' => 'Notification',
                'body' => 'You have a new notification.',
            ],
        };
    }
}
