<?php

namespace App\Policies;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\User;
use App\SchoolRole;

class LearningEnvironmentPolicy
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
    public function view(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher)
            || $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Student);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, School $school): bool
    {
        return $user->can('manageMemberships', $school);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->can('manageMemberships', $learningEnvironment->school);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->can('manageMemberships', $learningEnvironment->school);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return false;
    }
}
