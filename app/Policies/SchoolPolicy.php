<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

class SchoolPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, School $school): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin)
            || $user->hasApprovedSchoolMembership($school);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, School $school): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, School $school): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin);
    }

    public function manageMemberships(User $user, School $school): bool
    {
        return $user->hasRole(RoleSeeder::SuperAdmin)
            || $user->hasApprovedSchoolRole($school, SchoolRole::SchoolAdmin);
    }
}
