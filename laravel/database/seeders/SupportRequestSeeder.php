<?php

namespace Database\Seeders;

use App\Models\UserSupportRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportRequestSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        for ($i = 1; $i <= 200; $i++) {
            $type = fake()->randomElement([
                UserSupportRequest::TYPE_PHONE_NUMBER,
                UserSupportRequest::TYPE_USERNAME,
                UserSupportRequest::TYPE_EMAIL,
            ]);

            $status = fake()->randomElement([
                UserSupportRequest::STATUS_PENDING,
                UserSupportRequest::STATUS_APPROVED,
                UserSupportRequest::STATUS_REJECTED,
            ]);

            DB::table('user_support_requests')->insert([
                'user_id' => fake()->randomElement($userIds),
                'contact' => $this->makeContact($type),
                'type' => $type,
                'status' => $status,
                'note' => fake()->boolean(60) ? fake()->sentence() : null,
                'reviewed_at' => $status !== UserSupportRequest::STATUS_PENDING
                    ? now()->subDays(fake()->numberBetween(1, 30))
                    : null,
                'target' => fake()->randomElement([
                    UserSupportRequest::TARGET_FORGOT_PASSWORD,
                    UserSupportRequest::TARGET_BECOME_CREATOR,
                    UserSupportRequest::TARGET_BECOME_SGM,
                    UserSupportRequest::TARGET_CONTACT_SUPPORT,
                ]),
                'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                'updated_at' => now(),
            ]);
        }
    }

    private function makeContact(int $type): string
    {
        return match ($type) {
            UserSupportRequest::TYPE_PHONE_NUMBER => '05'.fake()->unique()->numerify('########'),
            UserSupportRequest::TYPE_USERNAME => fake()->userName(),
            UserSupportRequest::TYPE_EMAIL => fake()->unique()->safeEmail(),
            default => throw new \InvalidArgumentException("Invalid support request type: $type"),
        };
    }
}
