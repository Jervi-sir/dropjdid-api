<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreatorSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        // Pick around 40 creators from users
        $creatorIds = collect($userIds)
            ->random(min(40, count($userIds)))
            ->values()
            ->toArray();

        // Creator followers
        foreach ($creatorIds as $creatorId) {
            $followersCount = fake()->numberBetween(5, 80);

            foreach (fake()->randomElements($userIds, min($followersCount, count($userIds))) as $userId) {
                if ($userId === $creatorId) {
                    continue;
                }

                DB::table('creator_followers')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'creator_id' => $creatorId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Creator requests
        foreach (fake()->randomElements($userIds, min(80, count($userIds))) as $userId) {
            $status = fake()->randomElement([
                'pending',
                'approved',
                'approved',
                'rejected',
            ]);

            DB::table('creator_requests')->insert([
                'user_id' => $userId,
                'phone_number' => '05'.fake()->unique()->numerify('########'),
                'status' => $status,
                'note' => fake()->boolean(60) ? fake()->sentence() : null,
                'reviewed_at' => $status !== 'pending' ? now()->subDays(fake()->numberBetween(1, 30)) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
