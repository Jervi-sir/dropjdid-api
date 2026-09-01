<?php

namespace Database\Seeders;

use App\Models\CreatorFollower;
use App\Models\CreatorRequest;
use App\Models\Drop;
use App\Models\Label;
use App\Models\LikedDrop;
use App\Models\LikedProduct;
use App\Models\Product;
use App\Models\SavedDrop;
use App\Models\SavedLabel;
use App\Models\SavedProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaveLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $drops = Drop::all();
        $labels = Label::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // 1. Saved Products & Liked Products
            if ($products->isNotEmpty()) {
                $randomProducts = $products->random(min(rand(2, 4), $products->count()));
                foreach ($randomProducts as $product) {
                    SavedProduct::firstOrCreate([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);

                    LikedProduct::firstOrCreate([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);
                }
            }

            // 2. Saved Drops & Liked Drops
            if ($drops->isNotEmpty()) {
                $randomDrops = $drops->random(min(rand(2, 3), $drops->count()));
                foreach ($randomDrops as $drop) {
                    SavedDrop::firstOrCreate([
                        'user_id' => $user->id,
                        'drop_id' => $drop->id,
                    ]);

                    LikedDrop::firstOrCreate([
                        'user_id' => $user->id,
                        'drop_id' => $drop->id,
                    ]);
                }
            }

            // 3. Saved Labels
            if ($labels->isNotEmpty()) {
                $randomLabels = $labels->random(min(rand(2, 3), $labels->count()));
                foreach ($randomLabels as $label) {
                    SavedLabel::firstOrCreate([
                        'user_id' => $user->id,
                        'label_id' => $label->id,
                    ]);
                }
            }

            // 4. Creator Followers
            $otherUsers = $users->where('id', '!=', $user->id);
            if ($otherUsers->isNotEmpty()) {
                $creators = $otherUsers->random(min(rand(1, 2), $otherUsers->count()));
                foreach ($creators as $creator) {
                    CreatorFollower::firstOrCreate([
                        'user_id' => $user->id,
                        'creator_id' => $creator->id,
                    ]);
                }
            }

            // 5. Creator Requests (sample application)
            if ($user->id % 2 === 0) {
                CreatorRequest::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'phone_number' => $user->phone_number ?? '0555000000',
                        'request_status' => 'approved',
                        'note' => 'Streetwear fashion content creator and sneaker reviewer on TikTok/Instagram.',
                        'reviewed_at' => now(),
                    ]
                );
            }
        }
    }
}
