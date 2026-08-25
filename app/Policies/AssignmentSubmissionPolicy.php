<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\SchoolRole;

class AssignmentSubmissionPolicy
{
    public function view(User $user, AssignmentSubmission $assignmentSubmission): bool
    {
        return $user->is($assignmentSubmission->student)
            || $user->hasLearningEnvironmentRole($assignmentSubmission->learningEnvironment, SchoolRole::Teacher);
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $assignment->status === 'published'
            && $user->hasLearningEnvironmentRole($assignment->learningEnvironment, SchoolRole::Student);
    }

    public function evaluate(User $user, AssignmentSubmission $assignmentSubmission): bool
    {
        return $user->hasLearningEnvironmentRole($assignmentSubmission->learningEnvironment, SchoolRole::Teacher);
    }
}
