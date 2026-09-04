<?php

namespace App\Livewire\LearningMaterials;

use App\Livewire\Concerns\ResolvesTeacherEnvironments;
use App\Models\LearningEnvironment;
use App\Models\LearningMaterial;
use App\Models\User;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Learning Materials')]
class GlobalIndex extends Component
{
    use ResolvesTeacherEnvironments;

    public function mount(): void
    {
        $this->authorize('viewAny', LearningMaterial::class);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);

        $environmentIds = $this->getAccessibleEnvironmentIds();

        $environments = LearningEnvironment::whereKey($environmentIds)
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
