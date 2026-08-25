<?php

namespace App\Livewire\Academic;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Academic structure')]
class Manage extends Component
{
    public School $school;

    public string $subjectName = '';

    public string $subjectCode = '';

    public string $environmentName = '';

    public string $section = '';

    public string $subjectId = '';

    public function mount(School $school): void
    {
        $this->authorize('view', $school);
        $this->school = $school;
    }

    public function createSubject(): void
    {
        $this->authorize('create', [Subject::class, $this->school]);
        $data = $this->validate(['subjectName' => ['required', 'string', 'max:255'], 'subjectCode' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->where('school_id', $this->school->id)]]);
        $this->school->subjects()->create([
            'created_by' => $this->user()->id,
            'name' => $data['subjectName'],
            'code' => $data['subjectCode'],
        ]);
        $this->reset('subjectName', 'subjectCode');
        Flux::toast(variant: 'success', text: 'Subject created.');
    }

    public function createLearningEnvironment(): void
    {
        $this->authorize('create', [LearningEnvironment::class, $this->school]);
        $data = $this->validate(['environmentName' => ['required', 'string', 'max:255'], 'section' => ['nullable', 'string', 'max:100'], 'subjectId' => ['required', Rule::exists('subjects', 'id')->where('school_id', $this->school->id)]]);
        $subject = $this->school->subjects()->findOrFail($data['subjectId']);
        $this->authorize('update', $subject);

        $environment = LearningEnvironment::query()->create([
            'school_id' => $this->school->id,
            'subject_id' => $subject->id,
            'created_by' => $this->user()->id,
            'name' => $data['environmentName'],
            'section' => $data['section'] ?: null,
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
        $this->reset('environmentName', 'section', 'subjectId');
        Flux::toast(variant: 'success', text: 'Learning environment created.');
    }

    public function deleteSubject(int $id): void
    {
        $subject = $this->school->subjects()->findOrFail($id);
        $this->authorize('delete', $subject);
        $subject->delete();
        Flux::toast(text: 'Subject removed.');
    }

    public function deleteLearningEnvironment(int $id): void
    {
        $environment = $this->school->learningEnvironments()->findOrFail($id);
        $this->authorize('delete', $environment);
        $environment->delete();
        Flux::toast(text: 'Learning environment removed.');
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function canManageAcademic(): bool
    {
        return $this->user()->can('create', [Subject::class, $this->school]);
    }

    public function render()
    {
        $subjects = $this->school->subjects()
            ->with('learningEnvironments:id,subject_id,created_by,name,section')
            ->orderBy('name');

        if ($this->user()->role === SchoolRole::Teacher) {
            $subjects->where('created_by', $this->user()->id);
        }

        return view('livewire.academic.manage', ['subjects' => $subjects->get()]);
    }
}
