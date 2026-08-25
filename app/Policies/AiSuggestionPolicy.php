<?php

namespace App\Policies;

use App\Models\AiSuggestion;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;

class AiSuggestionPolicy
{
    public function view(User $user, AiSuggestion $aiSuggestion): bool
    {
        return $user->is($aiSuggestion->requester)
            && $user->hasLearningEnvironmentRole($aiSuggestion->learningEnvironment, SchoolRole::Teacher);
    }

    public function create(User $user, LearningEnvironment $learningEnvironment): bool
    {
        return $user->hasLearningEnvironmentRole($learningEnvironment, SchoolRole::Teacher);
    }

    public function update(User $user, AiSuggestion $aiSuggestion): bool
    {
        return $this->view($user, $aiSuggestion) && $aiSuggestion->status === 'draft';
    }

    public function approve(User $user, AiSuggestion $aiSuggestion): bool
    {
        return $this->view($user, $aiSuggestion) && $aiSuggestion->status === 'reviewed';
    }

    public function publish(User $user, AiSuggestion $aiSuggestion): bool
    {
        return $this->view($user, $aiSuggestion) && $aiSuggestion->status === 'approved';
    }
}
