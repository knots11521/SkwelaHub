<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;

class AssessmentPolicy
{
    public function view(User $user, Assessment $assessment): bool
    {
        return $user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Teacher)
            || ($assessment->status === 'published' && $user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Student));
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->is($assessment->author)
            && $user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Teacher);
    }

    public function viewResults(User $user, Assessment $assessment): bool
    {
        // 1. Allow Teachers to view all results for evaluation
        if ($user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Teacher)) {
            return true;
        }

        // 2. Allow Students to view results if they have submitted an attempt for this assessment
        return $assessment->attempts()
            ->where('student_id', $user->id)
            ->exists();
    }
}
