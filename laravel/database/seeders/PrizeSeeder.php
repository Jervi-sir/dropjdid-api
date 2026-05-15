<?php

namespace Database\Seeders;

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
                'status' => 'active',
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(10),
            ],
            [
                'title' => 'Previous Fashion Prize',
                'status' => 'ended',
                'starts_at' => now()->subDays(40),
                'ends_at' => now()->subDays(10),
            ],
            [
                'title' => 'Upcoming Creator Prize',
                'status' => 'draft',
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(21),
            ],
            [
                'title' => 'Cancelled Prize',
                'status' => 'cancelled',
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
                'joining_price' => fake()->randomElement([500, 1000, 1500, 2000]),
                'status' => $prize['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $joinedCount = match ($prize['status']) {
                'active' => 220,
                'ended' => 180,
                'draft' => 0,
                'cancelled' => 60,
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
                        'joined',
                        'joined',
                        'joined',
                        'cancelled',
                    ]),
                    'ended' => $userId === $winnerUserId ? 'winner' : fake()->randomElement([
                        'lost',
                        'lost',
                        'lost',
                        'refunded',
                    ]),
                    'cancelled' => fake()->randomElement([
                        'cancelled',
                        'refunded',
                    ]),
                    default => 'joined',
                };

                DB::table('prize_joinings')->updateOrInsert(
                    [
                        'prize_id' => $prizeId,
                        'user_id' => $userId,
                    ],
                    [
                        'amount_paid' => in_array($status, ['cancelled', 'refunded'])
                            ? 0
                            : DB::table('prizes')->where('id', $prizeId)->value('joining_price'),

                        'status' => $status,
                        'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
