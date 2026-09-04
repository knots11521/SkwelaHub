<?php

namespace App\Livewire\Concerns;

use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ResolvesTeacherEnvironments
{
    public function getAccessibleEnvironmentIds(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return LearningEnvironment::query()
            ->whereHas('memberships.schoolMembership', function ($query) use ($user) {
                $query->whereBelongsTo($user)->approved();
            })
            ->pluck('id')
            ->all();
    }
}
