<?php

namespace App\Policies;

use App\Models\LearningEnvironment;
use App\Models\LearningMaterial;
use App\Models\User;
use App\SchoolRole;

class LearningMaterialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            SchoolRole::Teacher->value,
            SchoolRole::Student->value,
            SchoolRole::ParentGuardian->value,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LearningMaterial $learningMaterial): bool
    {
        return $user->can('view', $learningMaterial->learningEnvironment);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->school_id === $learningEnvironment->school_id
            && $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LearningMaterial $learningMaterial): bool
    {
        return $user->is($learningMaterial->author)
            && $user->school_id === $learningMaterial->learningEnvironment->school_id
            && $user->hasLearningEnvironmentRole($learningMaterial->learningEnvironment, SchoolRole::Teacher);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LearningMaterial $learningMaterial): bool
    {
        return $this->update($user, $learningMaterial);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LearningMaterial $learningMaterial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LearningMaterial $learningMaterial): bool
    {
        return false;
    }
}
