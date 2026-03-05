<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'telegram_id' => fake()->unique()->numberBetween(100000000, 999999999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'username' => fake()->unique()->userName(),
            'phone' => '+998' . fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'language' => fake()->randomElement(['uz', 'ru']),
            'avatar_url' => null,
            'is_verified' => false,
            'is_blocked' => false,
            'last_active_at' => now(),
            'referral_code' => strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)),
            'balance' => 0,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    public function withBalance(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $amount,
        ]);
    }
}
