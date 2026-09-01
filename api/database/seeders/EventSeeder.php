<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $userId = $user?->id;

        $events = [
            [
                'user_id' => $userId,
                'title' => 'DropJdid Summer Fashion Fest 2026',
                'description' => 'Join the biggest streetwear and creator gathering in Algiers featuring exclusive drops, live pop-up stores, and music.',
                'image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800',
                'url' => 'https://dropjdid.com/events/summer-fashion-fest-2026',
                'status' => 'active',
                'sort_order' => 1,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(14),
                'meta' => [
                    'location' => 'Safex Exhibition Center, Algiers',
                    'city' => 'Algiers',
                    'badge' => 'Exclusive',
                    'cta_text' => 'Get Tickets',
                    'organizer' => 'DropJdid Official',
                    'capacity' => 1500,
                    'highlights' => ['Live Runway', 'Exclusive Creator Drops', 'Limited Merch'],
                ],
            ],
            [
                'user_id' => $userId,
                'title' => 'Sneakerhead Meetup & Trade Night',
                'description' => 'Bring your cleanest kicks, trade with fellow collectors, and get early access to upcoming limited product drops.',
                'image_url' => 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?w=800',
                'url' => 'https://dropjdid.com/events/sneaker-meetup-oran',
                'status' => 'active',
                'sort_order' => 2,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(7),
                'meta' => [
                    'location' => 'Le Meridien Hotel, Oran',
                    'city' => 'Oran',
                    'badge' => 'Community',
                    'cta_text' => 'RSVP Now',
                    'organizer' => 'DzStreetwear Club',
                    'capacity' => 500,
                    'highlights' => ['Sneaker Authentications', 'Prize Giveaways', 'Grail Auctions'],
                ],
            ],
            [
                'user_id' => $userId,
                'title' => 'Creator Masterclass: Building Drops that Sell Out',
                'description' => 'An online live workshop for fashion designers and digital creators on curating profitable drop campaigns.',
                'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800',
                'url' => 'https://dropjdid.com/events/creator-masterclass',
                'status' => 'active',
                'sort_order' => 3,
                'starts_at' => now()->addDays(3),
                'ends_at' => now()->addDays(10),
                'meta' => [
                    'location' => 'Online Stream (Live)',
                    'city' => 'Virtual',
                    'badge' => 'Masterclass',
                    'cta_text' => 'Register Free',
                    'organizer' => 'DropJdid Academy',
                    'capacity' => 2000,
                    'highlights' => ['Drop Strategies', 'Marketing Blueprint', 'Q&A Session'],
                ],
            ],
        ];

        foreach ($events as $eventData) {
            Event::updateOrCreate(
                ['title' => $eventData['title']],
                $eventData
            );
        }
    }
}
