<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployerProfile>
 */
class EmployerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'industry' => fake()->randomElement(['it', 'finance', 'education', 'medicine', 'retail', 'construction']),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'phone' => '+998' . fake()->numerify('#########'),
            'website' => fake()->url(),
            'logo_url' => null,
            'cover_url' => null,
            'employees_count' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'verification_level' => 'new',
            'rating' => 0,
            'rating_count' => 0,
            'response_time_avg' => 0,
        ];
    }
}
