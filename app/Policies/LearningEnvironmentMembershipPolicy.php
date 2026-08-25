<?php

namespace App\Policies;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\User;

class LearningEnvironmentMembershipPolicy
{
    public function view(User $user, LearningEnvironmentMembership $learningEnvironmentMembership): bool
    {
        return $user->can('manageMemberships', $learningEnvironmentMembership->learningEnvironment->school)
            || $user->is($learningEnvironmentMembership->schoolMembership->user);
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->can('manageMemberships', $learningEnvironment->school);
    }

    public function delete(User $user, LearningEnvironmentMembership $learningEnvironmentMembership): bool
    {
        return $user->can('manageMemberships', $learningEnvironmentMembership->learningEnvironment->school);
    }
}
