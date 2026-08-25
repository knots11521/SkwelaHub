<?php

namespace App\Policies;

use App\Models\SchoolMembership;
use App\Models\User;

class SchoolMembershipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchoolMembership $schoolMembership): bool
    {
        return $user->is($schoolMembership->user)
            || $user->can('manageMemberships', $schoolMembership->school);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SchoolMembership $schoolMembership): bool
    {
        return false;
    }

    public function approve(User $user, SchoolMembership $schoolMembership): bool
    {
        return false;
    }

    public function reject(User $user, SchoolMembership $schoolMembership): bool
    {
        return false;
    }
}
