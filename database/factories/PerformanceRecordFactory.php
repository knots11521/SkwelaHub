<?php

namespace Database\Factories;

use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\PerformanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceRecord>
 */
class PerformanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'learning_environment_id' => LearningEnvironment::factory(),
            'source_type' => AssignmentSubmission::class,
            'source_id' => fake()->unique()->randomNumber(),
            'score' => fake()->randomFloat(2, 0, 100),
            'recorded_at' => now(),
        ];
    }
}
