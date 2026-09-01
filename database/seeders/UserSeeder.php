<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'full_name' => 'Lamine Bekheira',
                'username' => 'lamine',
                'email' => 'lamine@dropjdid.com',
                'phone_number' => '0555000001',
            ],
            [
                'full_name' => 'Amine Creator',
                'username' => 'amine_creator',
                'email' => 'creator@dropjdid.com',
                'phone_number' => '0555000002',
            ],
            [
                'full_name' => 'Store Owner Dz',
                'username' => 'dz_store',
                'email' => 'store@dropjdid.com',
                'phone_number' => '0555000003',
            ],
            [
                'full_name' => 'Sarah Customer',
                'username' => 'sarah',
                'email' => 'sarah@dropjdid.com',
                'phone_number' => '0555000004',
            ],
            [
                'full_name' => 'Karim User',
                'username' => 'karim',
                'email' => 'karim@dropjdid.com',
                'phone_number' => '0555000005',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['username']],
                array_merge($data, [
                    'wilaya_id' => Wilaya::inRandomOrder()->value('id'),
                    'password' => Hash::make('password'),
                    'password_plaintext' => 'password',
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'image_url' => 'https://picsum.photos/seed/' . $data['username'] . '/200/200',
                    'is_active' => true,
                    'user_status' => 'approved',
                ])
            );

            $randomRoles = Role::inRandomOrder()->limit(rand(1, 2))->get();
            foreach ($randomRoles as $role) {
                UserRole::firstOrCreate([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
            }
        }
    }
}
