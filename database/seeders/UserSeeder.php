<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $wilayaIds = DB::table('wilayas')->pluck('id')->toArray();
        $roleIds = DB::table('roles')->pluck('id')->toArray();
        $platformIds = DB::table('social_platforms')->pluck('id')->toArray();

        $users = [];

        for ($i = 1; $i <= 100; $i++) {
            $users[] = User::create([
                'wilaya_id' => fake()->randomElement($wilayaIds),
                'full_name' => fake()->name(),
                'username' => 'user_'.$i,
                'phone_number' => '05'.fake()->unique()->numerify('########'),
                'phone_verified_at' => fake()->boolean(80) ? now() : null,
                'email' => 'user'.$i.'@example.com',
                'email_verified_at' => fake()->boolean(70) ? now() : null,
                'password' => Hash::make('password'),
                'password_plaintext' => 'password',
                'image' => fake()->boolean(50) ? ('https://fpoimg.com/600x400?text='.'user'.$i) : null,
                'is_active' => fake()->boolean(95),
            ]);
        }

        foreach ($users as $user) {
            // Random roles
            $randomRoles = fake()->randomElements($roleIds, fake()->numberBetween(1, min(2, count($roleIds))));

            foreach ($randomRoles as $roleId) {
                DB::table('user_roles')->updateOrInsert([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Random contacts
            foreach (fake()->randomElements($platformIds, fake()->numberBetween(0, min(3, count($platformIds)))) as $platformId) {
                DB::table('contacts')->insert([
                    'user_id' => $user->id,
                    'social_platform_id' => $platformId,
                    'url' => fake()->url(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Random friendships
        $userIds = collect($users)->pluck('id')->toArray();

        for ($i = 0; $i < 600; $i++) {
            $senderId = fake()->randomElement($userIds);
            $receiverId = fake()->randomElement($userIds);

            if ($senderId === $receiverId) {
                continue;
            }

            $status = fake()->randomElement([
                'pending',
                'accepted',
                'rejected',
                'blocked',
            ]);

            DB::table('friendships')->updateOrInsert([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
            ], [
                'status' => $status,
                'accepted_at' => $status === 'accepted' ? now() : null,
                'rejected_at' => $status === 'rejected' ? now() : null,
                'blocked_at' => $status === 'blocked' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
