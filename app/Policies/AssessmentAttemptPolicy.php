<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\SchoolRole;

class AssessmentAttemptPolicy
{
    public function view(User $user, AssessmentAttempt $assessmentAttempt): bool
    {
        return $user->is($assessmentAttempt->student)
            || $user->hasLearningEnvironmentRole($assessmentAttempt->learningEnvironment, SchoolRole::Teacher)
            || $user->isParentOf($assessmentAttempt->student);
    }

    public function create(User $user, Assessment $assessment): bool
    {
        return $assessment->status === 'published'
            && $user->hasLearningEnvironmentRole($assessment->learningEnvironment, SchoolRole::Student);
    }

    public function evaluate(User $user, AssessmentAttempt $assessmentAttempt): bool
    {
        return $user->hasLearningEnvironmentRole($assessmentAttempt->learningEnvironment, SchoolRole::Teacher);
    }
}
