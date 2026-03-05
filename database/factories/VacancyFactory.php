<?php

namespace Database\Factories;

use App\Models\EmployerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employer_id' => EmployerProfile::factory(),
            'language' => 'uz',
            'title_uz' => fake()->jobTitle(),
            'title_ru' => null,
            'category' => fake()->randomElement(['it', 'finance', 'education', 'medicine', 'retail', 'construction', 'logistics']),
            'description_uz' => fake()->paragraphs(3, true),
            'description_ru' => null,
            'requirements_uz' => fake()->paragraph(),
            'requirements_ru' => null,
            'responsibilities_uz' => fake()->paragraph(),
            'responsibilities_ru' => null,
            'salary_min' => fake()->numberBetween(1000000, 5000000),
            'salary_max' => fake()->numberBetween(5000000, 15000000),
            'salary_type' => fake()->randomElement(['fixed', 'negotiable', 'range']),
            'work_type' => fake()->randomElement(['full_time', 'part_time', 'remote', 'temporary']),
            'experience_required' => fake()->randomElement(['no_experience', '1-3', '3-5', '5+']),
            'city' => fake()->randomElement(['Toshkent', 'Samarqand', 'Buxoro', 'Namangan']),
            'district' => fake()->word(),
            'contact_phone' => '+998' . fake()->numerify('#########'),
            'contact_method' => 'telegram',
            'views_count' => 0,
            'applications_count' => 0,
            'status' => 'draft',
            'is_top' => false,
            'is_urgent' => false,
            'has_questionnaire' => false,
            'published_at' => null,
            'expires_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'published_at' => now(),
            'expires_at' => now()->addDays(15),
        ]);
    }
}
