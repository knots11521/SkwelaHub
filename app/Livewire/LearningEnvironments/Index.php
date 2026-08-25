<?php

namespace App\Livewire\LearningEnvironments;

use App\Models\LearningEnvironment;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Learning Environments')]
class Index extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);
        $isStudent = $user->hasRole(SchoolRole::Student->value);

        $environments = LearningEnvironment::query()
            ->select(['id', 'school_id', 'subject_id', 'name', 'section'])
            ->with(['school:id,name', 'subject:id,name,code'])
            ->whereHas('memberships.schoolMembership', function ($query) use ($user) {
                $query->whereBelongsTo($user)->approved();
            })
            ->latest()
            ->get()
            ->filter(fn (LearningEnvironment $environment): bool => $user->can('view', $environment));

        return view('livewire.learning-environments.index', [
            'environments' => $environments,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
        ]);
    }
}
