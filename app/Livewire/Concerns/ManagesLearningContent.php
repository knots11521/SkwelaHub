<?php

namespace App\Livewire\Concerns;

use App\SchoolRole;
use Illuminate\Support\Facades\Auth;

trait ManagesLearningContent
{
    public function canManage(): bool
    {
        return Auth::user()->hasLearningEnvironmentRole($this->learningEnvironment, SchoolRole::Teacher);
    }
}
