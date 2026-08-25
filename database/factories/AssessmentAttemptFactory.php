<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentAttempt>
 */
class AssessmentAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'learning_environment_id' => LearningEnvironment::factory(),
            'student_id' => User::factory(),
            'attempt' => 1,
            'status' => 'completed',
            'score' => 100,
            'submitted_at' => now(),
            'result_available_at' => now(),
        ];
    }
}
