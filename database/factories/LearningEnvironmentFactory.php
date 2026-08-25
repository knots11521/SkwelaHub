<?php

namespace Database\Factories;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningEnvironment>
 */
class LearningEnvironmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'subject_id' => Subject::factory(),
            'name' => fake()->words(2, true),
            'section' => fake()->randomLetter(),
        ];
    }
}
