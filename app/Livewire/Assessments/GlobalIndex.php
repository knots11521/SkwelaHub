<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningEnvironment;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Assessments')]
class GlobalIndex extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        $environmentIds = LearningEnvironment::whereHas('memberships.schoolMembership', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'approved');
        })->pluck('id');

        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);

        $assessments = Assessment::query()
            ->whereIn('learning_environment_id', $environmentIds)
            ->with(['author:id,name', 'learningEnvironment:id,name'])
            ->withCount(['attempts', 'questions'])
            ->when(! $isTeacher, fn($query) => $query->where('status', 'published'))
            ->latest()
            ->paginate(10);

        $attempts = AssessmentAttempt::query()
            ->where('student_id', $user->id)
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
