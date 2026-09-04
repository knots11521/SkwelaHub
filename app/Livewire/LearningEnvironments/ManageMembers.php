<?php

namespace App\Livewire\LearningEnvironments;

use App\Models\Invite;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageMembers extends Component
{
    public LearningEnvironment $learningEnvironment;

    public string $memberSearch = '';

    public ?int $selectedMemberId = null;

    public bool $showMemberDetailModal = false;

    public bool $showInviteModal = false;

    public ?string $classroomInviteExpiresAt = null;

    public string $classroomInviteRole = SchoolRole::Student->value;

    public ?int $classroomInviteStudentId = null;

    public ?Invite $lastGeneratedInvite = null;

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('manageMembers', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function openInviteModal(): void
    {
        $this->reset(['classroomInviteExpiresAt', 'classroomInviteStudentId', 'lastGeneratedInvite']);
        $this->classroomInviteRole = $this->availableClassroomRoles[0]->value ?? SchoolRole::Student->value;
        $this->showInviteModal = true;
    }

    public function generateClassroomInvite(): void
    {
        $this->authorize('manageMembers', $this->learningEnvironment);

        $validated = $this->validate([
            'classroomInviteRole' => ['required', 'string'],
            'classroomInviteExpiresAt' => ['nullable', 'date', 'after:today'],
            'classroomInviteStudentId' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('school_id', $this->learningEnvironment->school_id)],
        ]);

        $user = Auth::user();
        $role = SchoolRole::from($validated['classroomInviteRole']);

        if ($user->hasApprovedSchoolRole($this->learningEnvironment->school, SchoolRole::SchoolAdmin)) {
            $role = SchoolRole::Teacher;
        } elseif ($user->hasApprovedSchoolRole($this->learningEnvironment->school, SchoolRole::Teacher)) {
            $role = $role;
        } else {
            Flux::toast(variant: 'error', text: 'You are not authorized to generate invites.');

            return;
        }

        if ($role === SchoolRole::ParentGuardian && empty($validated['classroomInviteStudentId'])) {
            throw ValidationException::withMessages([
                'classroomInviteStudentId' => 'Please select a student for parent/guardian invites.',
            ]);
        }

        if ($role === SchoolRole::ParentGuardian && ! empty($validated['classroomInviteStudentId'])) {
            $student = User::query()->findOrFail($validated['classroomInviteStudentId']);

            if (! $student->hasRole(SchoolRole::Student->value)) {
                throw ValidationException::withMessages([
                    'classroomInviteStudentId' => 'The selected user is not a student.',
                ]);
            }
        }

        $invite = Invite::query()->create([
            'school_id' => $this->learningEnvironment->school_id,
            'learning_environment_id' => $this->learningEnvironment->id,
            'code' => strtoupper(Str::random(8)),
            'link_token' => Str::random(32),
            'role' => $role,
            'student_id' => $role === SchoolRole::ParentGuardian ? $validated['classroomInviteStudentId'] : null,
            'expires_at' => $validated['classroomInviteExpiresAt'] ? Carbon::parse($validated['classroomInviteExpiresAt']) : null,
            'created_by' => Auth::id(),
        ]);

        $this->lastGeneratedInvite = $invite;
        Flux::toast(variant: 'success', text: 'Classroom invite generated.');
    }

    public function revokeClassroomInvite(int $inviteId): void
    {
        $this->authorize('manageMembers', $this->learningEnvironment);

        $invite = Invite::query()->where('learning_environment_id', $this->learningEnvironment->id)->findOrFail($inviteId);
        $invite->delete();

        Flux::toast(variant: 'success', text: 'Classroom invite revoked.');
    }

    public function removeMember(int $id): void
    {
        $membership = $this->learningEnvironment->memberships()->findOrFail($id);
        $this->authorize('delete', $membership);
        $membership->delete();
        Flux::toast(text: 'Class membership removed.');
    }

    public function viewMember(int $id): void
    {
        $this->selectedMemberId = $id;
        $this->showMemberDetailModal = true;
    }

    #[Computed]
    public function memberships(): LengthAwarePaginator
    {
        $query = $this->learningEnvironment->memberships()
            ->with(['schoolMembership.user:id,name,email,created_at'])
            ->latest();

        if ($this->memberSearch !== '') {
            $query->whereHas('schoolMembership.user', fn ($query) => $query->where('name', 'like', '%'.$this->memberSearch.'%')->orWhere('email', 'like', '%'.$this->memberSearch.'%'));
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function selectedMember()
    {
        if ($this->selectedMemberId === null) {
            return null;
        }

        return $this->learningEnvironment->memberships()
            ->with(['schoolMembership.user', 'schoolMembership.reviewer'])
            ->find($this->selectedMemberId);
    }

    #[Computed]
    public function classroomInvites()
    {
        return Invite::query()
            ->where('learning_environment_id', $this->learningEnvironment->id)
            ->with('creator:id,name', 'student:id,name')
            ->latest()
            ->get();
    }

    #[Computed]
    public function students(): array
    {
        return User::query()
            ->where('school_id', $this->learningEnvironment->school_id)
            ->whereHas('roles', fn ($q) => $q->where('name', SchoolRole::Student->value))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    #[Computed]
    public function availableClassroomRoles(): array
    {
        $user = Auth::user();

        if ($user->hasApprovedSchoolRole($this->learningEnvironment->school, SchoolRole::SchoolAdmin)) {
            return [SchoolRole::Teacher];
        }

        if ($user->hasApprovedSchoolRole($this->learningEnvironment->school, SchoolRole::Teacher)) {
            return [SchoolRole::Student, SchoolRole::ParentGuardian];
        }

        return [];
    }

    public function render()
    {
        return view('livewire.learning-environments.manage-members');
    }
}
