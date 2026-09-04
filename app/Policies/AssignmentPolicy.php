<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            SchoolRole::Teacher->value,
            SchoolRole::Student->value,
            SchoolRole::ParentGuardian->value,
        ]);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Teacher)) {
            return true;
        }

        if ($assignment->status === 'published'
            && $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Student)) {
            return true;
        }

        return $assignment->status === 'published'
            && $user->isParentOfChildIn($assignment->learningEnvironment);
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->school_id === $learningEnvironment->school_id
            && $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->is($assignment->author)
            && $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Teacher);
    }

    public function viewSubmissions(User $user, Assignment $assignment): bool
    {
        return $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Teacher);
    }
}
