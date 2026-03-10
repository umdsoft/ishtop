<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkerProfile>
 */
class WorkerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'city' => fake()->randomElement(['Toshkent', 'Samarqand', 'Buxoro']),
            'specialty' => fake()->jobTitle(),
            'experience_years' => fake()->numberBetween(0, 15),
            'skills' => ['PHP', 'Laravel'],
            'expected_salary_min' => 2000000,
            'expected_salary_max' => 5000000,
            'work_types' => ['full_time'],
            'bio' => fake()->sentence(),
            'search_status' => 'open',
        ];
    }
}
