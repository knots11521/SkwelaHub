<?php

namespace Database\Factories;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\User;
use App\SchoolRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invite>
 */
class InviteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'learning_environment_id' => null,
            'code' => strtoupper(Str::random(8)),
            'link_token' => Str::random(32),
            'role' => SchoolRole::Student,
            'student_id' => null,
            'expires_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function forRole(SchoolRole $role): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => $role,
        ]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes): array => [
            'school_id' => $school->id,
        ]);
    }

    public function forLearningEnvironment(LearningEnvironment $learningEnvironment): static
    {
        return $this->state(fn (array $attributes): array => [
            'learning_environment_id' => $learningEnvironment->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_by' => $user->id,
        ]);
    }

    public function forStudent(?User $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'student_id' => $student?->id,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
