<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeJoining;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $prizesData = [
            [
                'title' => 'Win Air Jordan 1 Retro High Chicago 2026',
                'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=800',
                'description' => 'Join the grand community giveaway and get a chance to win the iconic Chicago 1s in your exact size!',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(5),
                'prize_status' => 'active',
            ],
            [
                'title' => 'Win Essentials FOG Oversized Hoodie (Beige)',
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800',
                'description' => 'Official heavyweight Essentials Fear of God hoodie giveaway. One lucky winner will be drawn live on stream.',
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(8),
                'prize_status' => 'active',
            ],
            [
                'title' => 'Exclusive Nike Dunk Low Panda Edition',
                'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800',
                'description' => 'Summer community appreciation drop! Enter your phone number to automatically qualify for the prize draw.',
                'starts_at' => now(),
                'ends_at' => now()->addDays(12),
                'prize_status' => 'active',
            ],
        ];

        foreach ($prizesData as $data) {
            $creator = $users->isNotEmpty() ? $users->random() : null;

            $prize = Prize::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'creator_id' => $creator?->id,
                ])
            );

            // Seed participants
            $participants = $users->random(min(rand(2, 4), $users->count()));
            foreach ($participants as $user) {
                PrizeJoining::firstOrCreate([
                    'prize_id' => $prize->id,
                    'user_id' => $user->id,
                ], [
                    'status' => 'joined',
                ]);
            }
        }
    }
}
