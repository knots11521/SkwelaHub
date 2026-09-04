<?php

namespace App\Policies;

use App\Models\PerformanceRecord;
use App\Models\User;
use App\SchoolRole;

class PerformanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(SchoolRole::Student->value)
            || ($user->hasRole(SchoolRole::ParentGuardian->value) && $user->students()->exists());
    }

    public function view(User $user, PerformanceRecord $performanceRecord): bool
    {
        return $user->is($performanceRecord->student)
            || $user->hasLearningEnvironmentRole($performanceRecord->learningEnvironment, SchoolRole::Teacher)
            || $user->isParentOf($performanceRecord->student);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PerformanceRecord $performanceRecord): bool
    {
        return false;
    }
}
