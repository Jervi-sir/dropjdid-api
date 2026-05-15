<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();

        $created = 0;

        while ($created < 120) {
            $firstUserId = fake()->randomElement($userIds);
            $secondUserId = fake()->randomElement($userIds);

            if ($firstUserId === $secondUserId) {
                continue;
            }

            $exists = DB::table('conversations')
                ->where(function ($query) use ($firstUserId, $secondUserId) {
                    $query->where('first_user_id', $firstUserId)
                        ->where('second_user_id', $secondUserId);
                })
                ->orWhere(function ($query) use ($firstUserId, $secondUserId) {
                    $query->where('first_user_id', $secondUserId)
                        ->where('second_user_id', $firstUserId);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            $conversationId = DB::table('conversations')->insertGetId([
                'type' => fake()->randomElement(['private', 'private', 'support']),
                'first_user_id' => $firstUserId,
                'second_user_id' => $secondUserId,
                'first_user_last_read_at' => fake()->boolean(70) ? now()->subMinutes(fake()->numberBetween(1, 5000)) : null,
                'second_user_last_read_at' => fake()->boolean(70) ? now()->subMinutes(fake()->numberBetween(1, 5000)) : null,
                'created_at' => now()->subDays(fake()->numberBetween(0, 90)),
                'updated_at' => now(),
            ]);

            $messagesCount = fake()->numberBetween(1, 40);

            for ($i = 1; $i <= $messagesCount; $i++) {
                $type = fake()->randomElement([
                    'text',
                    'text',
                    'text',
                    'image',
                    'product',
                    'profile',
                ]);

                $attachableType = null;
                $attachableId = null;
                $body = fake()->sentence();

                if ($type === 'image') {
                    $body = 'https://fpoimg.com/400x800?text='.'fashion'.$i;
                }

                if ($type === 'product' && ! empty($productIds)) {
                    $attachableType = 'App\\Models\\Product';
                    $attachableId = fake()->randomElement($productIds);
                    $body = fake()->boolean(50) ? 'Check this product' : null;
                }

                if ($type === 'profile') {
                    $attachableType = 'App\\Models\\User';
                    $attachableId = fake()->randomElement($userIds);
                    $body = fake()->boolean(50) ? 'Check this profile' : null;
                }

                DB::table('messages')->insert([
                    'conversation_id' => $conversationId,
                    'sender_id' => fake()->randomElement([$firstUserId, $secondUserId]),
                    'type' => $type,
                    'body' => $body,
                    'attachable_type' => $attachableType,
                    'attachable_id' => $attachableId,
                    'created_at' => now()->subMinutes(fake()->numberBetween(1, 100000)),
                    'updated_at' => now(),
                ]);
            }

            $created++;
        }
    }
}
