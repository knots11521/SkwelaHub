<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        return $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Teacher)
            || ($assignment->status === 'published' && $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Student));
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
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
