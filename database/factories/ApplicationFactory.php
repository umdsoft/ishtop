<?php

namespace Database\Factories;

use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'worker_id' => WorkerProfile::factory(),
            'stage' => 'new',
            'cover_letter' => fake()->sentence(),
        ];
    }
}
