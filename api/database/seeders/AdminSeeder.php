<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Admin Model (admins table)
        Admin::firstOrCreate(
            ['email' => 'admin@dropjdid.com'],
            [
                'username' => 'admin',
                'phone_number' => '0558054300',
                'password' => Hash::make('password'),
                'password_plaintext' => 'password',
                'is_approved' => true,
            ]
        );

        // 2. Seed Admin User & Role (users table)
        $adminRole = Role::firstOrCreate(
            ['code' => 'admin'],
            [
                'en' => 'Admin',
                'fr' => 'Administrateur',
                'ar' => 'مشرف',
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@dropjdid.com'],
            [
                'username' => 'admin',
                'full_name' => 'System Administrator',
                'phone_number' => '0558054300',
                'wilaya_id' => Wilaya::inRandomOrder()->value('id'),
                'password' => Hash::make('password'),
                'password_plaintext' => 'password',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'image_url' => 'https://picsum.photos/seed/admin/200/200',
                'is_active' => true,
                'user_status' => 'approved',
            ]
        );

        UserRole::firstOrCreate([
            'user_id' => $adminUser->id,
            'role_id' => $adminRole->id,
        ]);
    }
}
