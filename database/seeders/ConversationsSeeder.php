<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Drop;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ConversationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $drops = Drop::all();

        if ($users->count() < 2 || $products->isEmpty() || $drops->isEmpty()) {
            return;
        }

        $this->seedConversations($users, $products, $drops);
    }

    private function seedConversations(Collection $users, Collection $products, Collection $drops): void
    {
        foreach (range(1, 8) as $index) {
            $type = collect(['private', 'support'])->random();
            $participants = $users->shuffle()->take(2)->values();

            if ($participants->count() < 2) {
                continue;
            }

            $conversation = Conversation::query()->create([
                'type' => $type,
                'first_user_id' => $participants[0]->id,
                'second_user_id' => $participants[1]->id,
                'first_user_last_read_at' => now()->subMinutes(random_int(1, 1440)),
                'second_user_last_read_at' => now()->subMinutes(random_int(1, 1440)),
            ]);

            foreach (range(1, random_int(2, 5)) as $messageIndex) {
                $messageType = collect(['text', 'product', 'image'])->random();
                $attachable = null;

                if ($messageType === 'product') {
                    $attachable = $products->random();
                } elseif ($messageType === 'image') {
                    $attachable = $drops->random();
                }

                Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $participants->random()->id,
                    'type' => $messageType,
                    'body' => $messageType === 'text' ? fake()->sentence() : null,
                    'attachable_type' => $attachable?->getMorphClass(),
                    'attachable_id' => $attachable?->id,
                ]);
            }
        }
    }
}
