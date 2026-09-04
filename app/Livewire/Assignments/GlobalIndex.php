<?php

namespace App\Livewire\Assignments;

use App\Livewire\Concerns\ResolvesTeacherEnvironments;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Assignments')]
class GlobalIndex extends Component
{
    use ResolvesTeacherEnvironments;

    public function mount(): void
    {
        $this->authorize('viewAny', Assignment::class);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);
        $isParent = $user->hasRole(SchoolRole::ParentGuardian->value);

        $environmentIds = $this->getAccessibleEnvironmentIds();

        $environments = LearningEnvironment::whereKey($environmentIds)
            ->with(['assignments' => function ($query) use ($isTeacher) {
                $query->when(! $isTeacher, fn ($q) => $q->published())->latest();
            }])
            ->get();

        $assignmentIds = $environments->pluck('assignments')->flatten()->pluck('id');

        $childIds = $isParent ? $user->students()->pluck('users.id')->all() : [$user->id];

        $submissions = AssignmentSubmission::query()
            ->whereIn('student_id', $childIds)
            ->whereIn('assignment_id', $assignmentIds)
            ->latest('attempt')
            ->get()
            ->keyBy('assignment_id');

        return view('livewire.assignments.global-index', [
            'environments' => $environments,
            'submissions' => $submissions,
            'isTeacher' => $isTeacher,
        ]);
    }
}
