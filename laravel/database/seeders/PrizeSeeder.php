<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeJoining;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrizeSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        $prizes = [
            [
                'title' => 'Current Mega Prize',
                'status' => Prize::STATUS_ACTIVE,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(10),
            ],
            [
                'title' => 'Previous Fashion Prize',
                'status' => Prize::STATUS_ENDED,
                'starts_at' => now()->subDays(40),
                'ends_at' => now()->subDays(10),
            ],
            [
                'title' => 'Upcoming Creator Prize',
                'status' => Prize::STATUS_DRAFT,
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(21),
            ],
            [
                'title' => 'Cancelled Prize',
                'status' => Prize::STATUS_CANCELLED,
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->subDays(5),
            ],
        ];

        foreach ($prizes as $index => $prize) {
            $prizeId = DB::table('prizes')->insertGetId([
                'creator_id' => fake()->randomElement($userIds),
                'title' => $prize['title'],
                'image' => 'https://fpoimg.com/400x800?text='.'fashion'.$index,
                'description' => fake()->paragraph(),
                'starts_at' => $prize['starts_at'],
                'ends_at' => $prize['ends_at'],
                'status' => $prize['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $joinedCount = match ($prize['status']) {
                Prize::STATUS_ACTIVE => 220,
                Prize::STATUS_ENDED => 180,
                Prize::STATUS_DRAFT => 0,
                Prize::STATUS_CANCELLED => 60,
                default => 0,
            };

            if ($joinedCount === 0) {
                continue;
            }

            $joinedUserIds = fake()->randomElements(
                $userIds,
                min($joinedCount, count($userIds))
            );

            $winnerUserId = $prize['status'] === 'ended'
                ? fake()->randomElement($joinedUserIds)
                : null;

            foreach ($joinedUserIds as $userId) {
                $status = match ($prize['status']) {
                    'active' => fake()->randomElement([
                        PrizeJoining::STATUS_JOINED,
                        PrizeJoining::STATUS_CANCELLED,
                    ]),
                    'ended' => $userId === $winnerUserId ? PrizeJoining::STATUS_WINNER : fake()->randomElement([
                        PrizeJoining::STATUS_LOST,
                        PrizeJoining::STATUS_REFUNDED,
                    ]),
                    'cancelled' => fake()->randomElement([
                        PrizeJoining::STATUS_CANCELLED,
                        PrizeJoining::STATUS_REFUNDED,
                    ]),
                    default => PrizeJoining::STATUS_JOINED,
                };

                DB::table('prize_joinings')->updateOrInsert(
                    [
                        'prize_id' => $prizeId,
                        'user_id' => $userId,
                    ],
                    [
                        'status' => $status,
                        'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
