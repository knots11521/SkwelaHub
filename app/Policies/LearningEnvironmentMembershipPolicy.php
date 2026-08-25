<?php

namespace App\Policies;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\User;
use App\SchoolRole;

class LearningEnvironmentMembershipPolicy
{
    public function view(User $user, LearningEnvironmentMembership $learningEnvironmentMembership): bool
    {
        return $user->can('manageMemberships', $learningEnvironmentMembership->learningEnvironment->school)
            || $user->is($learningEnvironmentMembership->schoolMembership->user);
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->can('manageMembers', $learningEnvironment);
    }

    public function delete(User $user, LearningEnvironmentMembership $learningEnvironmentMembership): bool
    {
        return $user->can('manageMembers', $learningEnvironmentMembership->learningEnvironment)
            && ($user->can('manageMemberships', $learningEnvironmentMembership->learningEnvironment->school)
                || $learningEnvironmentMembership->schoolMembership->requested_role === SchoolRole::Student);
    }
}
