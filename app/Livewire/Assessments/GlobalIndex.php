<?php

namespace App\Livewire\Assessments;

use App\Livewire\Concerns\ResolvesTeacherEnvironments;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Assessments')]
class GlobalIndex extends Component
{
    use ResolvesTeacherEnvironments;

    public function mount(): void
    {
        $this->authorize('viewAny', Assessment::class);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);
        $isParent = $user->hasRole(SchoolRole::ParentGuardian->value);

        $environmentIds = $this->getAccessibleEnvironmentIds();

        $query = Assessment::query()
            ->with(['author:id,name', 'learningEnvironment:id,name'])
            ->withCount(['attempts', 'questions'])
            ->latest();

        if ($isParent) {
            $childIds = $user->students()->pluck('users.id')->all();
            $attemptedAssessmentIds = AssessmentAttempt::query()
                ->whereIn('student_id', $childIds)
                ->pluck('assessment_id')
                ->all();

            $query->where(function ($q) use ($environmentIds, $attemptedAssessmentIds) {
                $q->whereIn('id', $attemptedAssessmentIds)
                    ->orWhereIn('learning_environment_id', $environmentIds);
            })->where('status', 'published');
        } else {
            $query->whereIn('learning_environment_id', $environmentIds)
                ->when(! $isTeacher, fn ($q) => $q->where('status', 'published'));
        }

        $assessments = $query->paginate(10);

        $attempts = AssessmentAttempt::query()
            ->whereIn('student_id', $isParent ? $user->students()->pluck('users.id')->all() : [$user->id])
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        return view('livewire.assessments.global-index', [
            'assessments' => $assessments,
            'attempts' => $attempts,
            'isTeacher' => $isTeacher,
        ]);
    }
}
