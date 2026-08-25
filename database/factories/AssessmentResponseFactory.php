<?php

namespace Database\Factories;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentResponse>
 */
class AssessmentResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_attempt_id' => AssessmentAttempt::factory(),
            'assessment_question_id' => AssessmentQuestion::factory(),
            'response' => 'Option one',
            'is_correct' => true,
        ];
    }
}
