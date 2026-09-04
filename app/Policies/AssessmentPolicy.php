<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            SchoolRole::Teacher->value,
            SchoolRole::Student->value,
            SchoolRole::ParentGuardian->value,
        ]);
    }

    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Teacher)) {
            return true;
        }

        if ($assessment->status === 'published'
            && $user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Student)) {
            return true;
        }

        return $assessment->status === 'published'
            && $user->isParentOfChildIn($assessment->learningEnvironment);
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->school_id === $learningEnvironment->school_id
            && $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
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

        // 2. Allow Parents of a student who has attempted this assessment
        if ($user->hasRole(SchoolRole::ParentGuardian->value)
            && $assessment->attempts()->whereIn('student_id', $user->linkedStudentIds())->exists()) {
            return true;
        }

        // 3. Allow Students to view results if they have submitted an attempt for this assessment
        return $assessment->attempts()
            ->where('student_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the assessment.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }
}
