<?php

namespace App\Livewire\LearningEnvironments;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Learning Environments')]
class Index extends Component
{
    public string $environmentName = '';

    public string $subjectName = '';

    public ?int $editingEnvironmentId = null;

    public bool $showCreateEnvironmentModal = false;

    public bool $showEditEnvironmentModal = false;

    public string $environmentSearch = '';

    public function render(): View
    {
        $user = Auth::user();

        $isTeacher = $user->hasRole(SchoolRole::Teacher->value);
        $isStudent = $user->hasRole(SchoolRole::Student->value);

        return view('livewire.learning-environments.index', [
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
        ]);
    }

    #[Computed]
    public function environments(): LengthAwarePaginator
    {
        $user = Auth::user();

        $query = LearningEnvironment::query()
            ->select(['id', 'school_id', 'subject_id', 'name', 'section'])
            ->with(['school:id,name', 'subject:id,name', 'memberships' => fn ($query) => $query->with('schoolMembership.user:id,name,email')])
            ->whereHas('memberships.schoolMembership', function ($query) use ($user) {
                $query->whereBelongsTo($user)->approved();
            })
            ->latest();

        if ($this->environmentSearch !== '') {
            $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->environmentSearch.'%')
                    ->orWhereHas('subject', fn ($query) => $query->where('name', 'like', '%'.$this->environmentSearch.'%'))
                    ->orWhereHas('school', fn ($query) => $query->where('name', 'like', '%'.$this->environmentSearch.'%'));
            });
        }

        $filtered = $query->get()->filter(fn (LearningEnvironment $environment): bool => $user->can('view', $environment));
        $page = request()->query('page', 1);
        $perPage = 12;
        $items = $filtered->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($items, $filtered->count(), $perPage, $page, ['path' => request()->url()]);
    }

    public function createLearningEnvironment(): void
    {
        $this->authorize('create', [LearningEnvironment::class, $this->school]);
        $data = $this->validate([
            'environmentName' => ['required', 'string', 'max:255'],
            'subjectName' => ['required', 'string', 'max:255'],
        ]);

        $subject = $this->school->subjects()->create([
            'created_by' => $this->user()->id,
            'name' => $data['subjectName'],
        ]);

        $environment = LearningEnvironment::query()->create([
            'school_id' => $this->school->id,
            'subject_id' => $subject->id,
            'created_by' => $this->user()->id,
            'name' => $data['environmentName'],
        ]);

        $membership = SchoolMembership::query()
            ->whereBelongsTo($this->user())
            ->whereBelongsTo($this->school)
            ->where('status', SchoolMembershipStatus::Approved)
            ->where('requested_role', SchoolRole::Teacher->value)
            ->firstOrFail();

        LearningEnvironmentMembership::query()->firstOrCreate([
            'school_membership_id' => $membership->id,
            'learning_environment_id' => $environment->id,
        ]);
        $this->resetEnvironmentForm();
        $this->showCreateEnvironmentModal = false;
        Flux::toast(variant: 'success', text: 'Classroom created.');
    }

    private function resetEnvironmentForm(): void
    {
        $this->reset('environmentName', 'subjectName');
    }

    public function openCreateEnvironmentModal(): void
    {
        $this->resetEnvironmentForm();
        $this->showCreateEnvironmentModal = true;
    }

    public function editLearningEnvironment(int $id): void
    {
        $environment = $this->school->learningEnvironments()->findOrFail($id);
        $this->authorize('update', $environment);
        $this->editingEnvironmentId = $id;
        $this->environmentName = $environment->name;
        $this->subjectName = $environment->subject?->name ?? '';
        $this->showEditEnvironmentModal = true;
    }

    public function updateLearningEnvironment(): void
    {
        $environment = $this->school->learningEnvironments()->findOrFail($this->editingEnvironmentId);
        $this->authorize('update', $environment);
        $data = $this->validate([
            'environmentName' => ['required', 'string', 'max:255'],
            'subjectName' => ['required', 'string', 'max:255'],
        ]);

        $subject = $environment->subject;
        $this->authorize('update', $subject);
        $subject->update(['name' => $data['subjectName']]);

        $environment->update([
            'name' => $data['environmentName'],
        ]);
        $this->reset('environmentName', 'subjectName', 'editingEnvironmentId');
        $this->showEditEnvironmentModal = false;
        Flux::toast(variant: 'success', text: 'Classroom updated.');
    }

    public function deleteLearningEnvironment(int $id): void
    {
        $environment = $this->school->learningEnvironments()->findOrFail($id);
        $this->authorize('delete', $environment);
        $environment->delete();
        Flux::toast(text: 'Classroom removed.');
    }

    #[Computed]
    public function editingEnvironment()
    {
        if ($this->editingEnvironmentId === null) {
            return null;
        }

        return $this->school->learningEnvironments()->find($this->editingEnvironmentId);
    }

    #[Computed]
    public function school(): School
    {
        return $this->user()->school;
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
