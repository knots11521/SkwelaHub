<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_environment_id' => LearningEnvironment::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'instructions' => fake()->paragraph(),
            'status' => 'draft',
        ];
    }
}
