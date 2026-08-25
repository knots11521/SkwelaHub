<?php

namespace Database\Factories;

use App\Models\AiSuggestion;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSuggestion>
 */
class AiSuggestionFactory extends Factory
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
            'requested_by' => User::factory(),
            'kind' => 'assignment',
            'prompt' => fake()->paragraph(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'status' => 'draft',
        ];
    }
}
