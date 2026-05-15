<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportRequestSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        for ($i = 1; $i <= 200; $i++) {
            $type = fake()->randomElement([
                'phone_number',
                'username',
                'email',
            ]);

            $status = fake()->randomElement([
                'pending',
                'approved',
                'approved',
                'rejected',
            ]);

            DB::table('user_support_requests')->insert([
                'user_id' => fake()->randomElement($userIds),
                'contact' => $this->makeContact($type),
                'type' => $type,
                'status' => $status,
                'note' => fake()->boolean(60) ? fake()->sentence() : null,
                'reviewed_at' => $status !== 'pending'
                    ? now()->subDays(fake()->numberBetween(1, 30))
                    : null,
                'target' => fake()->randomElement([
                    'forgot-password',
                    'become-creator',
                    'become-sgm',
                    'contact-support',
                ]),
                'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                'updated_at' => now(),
            ]);
        }
    }

    private function makeContact(string $type): string
    {
        return match ($type) {
            'phone_number' => '05'.fake()->unique()->numerify('########'),
            'username' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
        };
    }
}
