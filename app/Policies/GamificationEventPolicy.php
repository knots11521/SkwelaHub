<?php

namespace App\Policies;

use App\Models\GamificationEvent;
use App\Models\User;
use App\SchoolRole;

class GamificationEventPolicy
{
    public function view(User $user, GamificationEvent $gamificationEvent): bool
    {
        return $user->is($gamificationEvent->user)
            || $user->hasLearningEnvironmentRole($gamificationEvent->learningEnvironment, SchoolRole::Teacher)
            || $user->isParentOf($gamificationEvent->user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, GamificationEvent $gamificationEvent): bool
    {
        return false;
    }
}
