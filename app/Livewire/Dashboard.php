<?php

namespace App\Livewire;

use App\Livewire\Concerns\ResolvesTeacherEnvironments;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use ResolvesTeacherEnvironments;

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole(RoleSeeder::SuperAdmin);
        $isSchoolAdmin = $user->hasRole(SchoolRole::SchoolAdmin->value);
        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);
        $isStudent = $user->hasRole(SchoolRole::Student->value);
        $isParent = $user->hasRole(SchoolRole::ParentGuardian->value);
        $isGuestMode = ! $user->hasApprovedSchoolMembership();
        $canJoinSchool = $user->canAccessJoinSchool();

        $schools = School::query()->active()->select(['id', 'name', 'slug'])->latest()->get();

        $environmentIds = $this->getAccessibleEnvironmentIds();

        $environments = LearningEnvironment::query()
            ->whereKey($environmentIds)
            ->with(['school:id,name', 'subject:id,name'])
            ->orderBy('name')
            ->get();

        $adminSchools = $schools->filter(fn (School $school): bool => $user->hasApprovedSchoolRole($school, SchoolRole::SchoolAdmin));
        $adminSchoolIds = $adminSchools->modelKeys();

        return view('livewire.dashboard', [
            'user' => $user,
            'schools' => $schools,
            'environments' => $environments,
            'adminSchools' => $adminSchools,
            'isSuperAdmin' => $isSuperAdmin,
            'isSchoolAdmin' => $isSchoolAdmin,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
            'isParent' => $isParent,
            'isGuestMode' => $isGuestMode,
            'canJoinSchool' => $canJoinSchool,
            'platformStats' => [
                'schools' => $isSuperAdmin ? $schools->count() : 0,
                'users' => $isSuperAdmin ? User::query()->count() : 0,
            ],
            'schoolAdministrators' => $isSuperAdmin
                ? User::query()->whereHas('roles', fn ($q) => $q->where('name', SchoolRole::SchoolAdmin->value))->with('school:id,name')->orderBy('name')->get(['id', 'school_id', 'name', 'email'])
                : collect(),
            'schoolStats' => [
                'members' => $isSchoolAdmin ? SchoolMembership::query()->approved()->whereIn('school_id', $adminSchoolIds)->count() : 0,
                'subjects' => $isSchoolAdmin ? Subject::query()->whereIn('school_id', $adminSchoolIds)->count() : 0,
                'environments' => $isSchoolAdmin ? LearningEnvironment::query()->whereIn('school_id', $adminSchoolIds)->count() : 0,
            ],
            'teacherStats' => [
                'assignments' => $isTeacher ? Assignment::query()->whereIn('learning_environment_id', $environmentIds)->count() : 0,
                'assessments' => $isTeacher ? Assessment::query()->whereIn('learning_environment_id', $environmentIds)->count() : 0,
                'pendingSubmissions' => $isTeacher ? AssignmentSubmission::query()->whereIn('learning_environment_id', $environmentIds)->where('evaluation_status', 'pending')->count() : 0,
                'pendingAttempts' => $isTeacher ? AssessmentAttempt::query()->whereIn('learning_environment_id', $environmentIds)->where('evaluation_status', 'pending')->count() : 0,
            ],
            'studentStats' => [
                'assignments' => $isStudent ? Assignment::query()->published()->whereIn('learning_environment_id', $environmentIds)->whereDoesntHave('submissions', fn ($query) => $query->whereBelongsTo($user, 'student'))->count() : 0,
                'assessments' => $isStudent ? Assessment::query()->published()->whereIn('learning_environment_id', $environmentIds)->whereDoesntHave('attempts', fn ($query) => $query->whereBelongsTo($user, 'student'))->count() : 0,
                'evaluatedWork' => $isStudent ? $user->performanceRecords()->count() : 0,
                'points' => $isStudent ? $user->gamificationEvents()->sum('points') : 0,
            ],
        ]);
    }
}
