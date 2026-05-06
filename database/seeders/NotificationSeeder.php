<?php

namespace Database\Seeders;

use App\Models\Drop;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Order;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $targets = collect([...Order::all(), ...Drop::all(), ...Prize::all()]);

        if ($users->isEmpty() || $targets->isEmpty()) {
            return;
        }

        foreach (range(1, 16) as $index) {
            $notificationType = NotificationType::query()->inRandomOrder()->first();
            $user = $users->random();
            $notifiable = $targets->random();

            if ($notificationType === null) {
                continue;
            }

            Notification::query()->create([
                'notification_type_id' => $notificationType->id,
                'user_id' => $user->id,
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id,
                'data' => [
                    'title' => fake()->sentence(3),
                    'message' => fake()->sentence(),
                ],
                'read_at' => fake()->boolean(50) ? now()->subHours(random_int(1, 72)) : null,
            ]);
        }
    }
}
