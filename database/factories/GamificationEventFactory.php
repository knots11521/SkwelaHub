<?php

namespace Database\Factories;

use App\Models\AssignmentSubmission;
use App\Models\GamificationEvent;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamificationEvent>
 */
class GamificationEventFactory extends Factory
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
            'learning_environment_id' => LearningEnvironment::factory(),
            'source_type' => AssignmentSubmission::class,
            'source_id' => fake()->unique()->randomNumber(),
            'points' => 10,
            'reason' => 'Assignment completed',
            'awarded_at' => now(),
        ];
    }
}
