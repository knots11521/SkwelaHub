<?php

namespace App\Livewire\Assignments;

use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\SchoolRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Assignments')]
class GlobalIndex extends Component
{
    public function render(): View
    {
        $user = Auth::user();
        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);

        $environments = LearningEnvironment::whereHas('memberships.schoolMembership', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'approved');
        })
            ->with(['assignments' => function ($query) use ($isTeacher) {
                $query->when(! $isTeacher, fn($q) => $q->published())->latest();
            }])
            ->get();

        $assignmentIds = $environments->pluck('assignments')->flatten()->pluck('id');

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $user->id)
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
