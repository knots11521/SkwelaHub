<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentSubmission>
 */
class AssignmentSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'learning_environment_id' => LearningEnvironment::factory(),
            'student_id' => User::factory(),
            'attempt' => 1,
            'status' => 'submitted',
            'content' => fake()->paragraph(),
            'submitted_at' => now(),
        ];
    }
}
