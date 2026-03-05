<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['balance_topup', 'subscription', 'vacancy_top', 'vacancy_urgent']),
            'amount' => fake()->numberBetween(10000, 500000),
            'method' => fake()->randomElement(['payme', 'click', 'uzum', 'balance']),
            'status' => 'pending',
            'external_id' => null,
            'payable_type' => null,
            'payable_id' => null,
            'meta' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'external_id' => 'ext_' . fake()->uuid(),
        ]);
    }
}
