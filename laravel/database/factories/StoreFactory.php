<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wilaya_id' => null,
            'store_name' => $this->faker->company(),
            'phone_number' => $this->faker->phoneNumber(),
            'password' => Hash::make('password'),
            'password_plaintext' => 'password123',
            'logo' => null,
            'description' => $this->faker->sentence(),
            'balance' => 0.00,
            'status' => Store::STATUS_PENDING,
            'is_verified' => false,
        ];
    }
}
