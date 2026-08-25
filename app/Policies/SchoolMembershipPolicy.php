<?php

namespace App\Policies;

use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

class SchoolMembershipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin)
            || $user->schoolMemberships()
                ->approved()
                ->whereIn('requested_role', [SchoolRole::SchoolAdmin->value, SchoolRole::Teacher->value])
                ->exists();
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SchoolMembership $schoolMembership): bool
    {
        return $user->is($schoolMembership->user)
            && in_array($schoolMembership->status, [
                SchoolMembershipStatus::Rejected,
                SchoolMembershipStatus::Removed,
            ], true);
    }

    public function approve(User $user, SchoolMembership $schoolMembership): bool
    {
        if ($schoolMembership->status !== SchoolMembershipStatus::Pending) {
            return false;
        }

        return match ($schoolMembership->requested_role) {
            SchoolRole::SchoolAdmin => $user->hasRole(RoleSeeder::SuperAdmin),
            SchoolRole::Teacher => $user->hasApprovedSchoolRole($schoolMembership->school, SchoolRole::SchoolAdmin),
            SchoolRole::Student, SchoolRole::ParentGuardian => $user->hasApprovedSchoolRole($schoolMembership->school, SchoolRole::Teacher),
        };
    }

    public function reject(User $user, SchoolMembership $schoolMembership): bool
    {
        return $this->approve($user, $schoolMembership);
    }
}
