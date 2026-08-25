<?php

namespace App\Livewire\LearningMaterials;

use App\Models\LearningEnvironment;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Learning Materials')]
class GlobalIndex extends Component
{
    public function render(): View
    {
        $user = Auth::user();
        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);

        $environments = LearningEnvironment::whereHas('memberships.schoolMembership', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'approved');
        })
            ->with(['materials' => function ($query) {
                $query->latest();
            }])
            ->get();

        return view('livewire.learning-materials.global-index', [
            'environments' => $environments,
            'isTeacher' => $isTeacher,
        ]);
    }
}
