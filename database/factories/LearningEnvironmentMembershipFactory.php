<?php

namespace Database\Factories;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\SchoolMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningEnvironmentMembership>
 */
class LearningEnvironmentMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_membership_id' => SchoolMembership::factory(),
            'learning_environment_id' => LearningEnvironment::factory(),
        ];
    }
}
