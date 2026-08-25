<?php

namespace App\Livewire\Academic;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
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
        $this->authorize('manageMemberships', $school);
        $this->school = $school;
    }

    public function createSubject(): void
    {
        $this->authorize('create', [Subject::class, $this->school]);
        $data = $this->validate(['subjectName' => ['required', 'string', 'max:255'], 'subjectCode' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->where('school_id', $this->school->id)]]);
        $this->school->subjects()->create(['name' => $data['subjectName'], 'code' => $data['subjectCode']]);
        $this->reset('subjectName', 'subjectCode');
        Flux::toast(variant: 'success', text: 'Subject created.');
    }

    public function createLearningEnvironment(): void
    {
        $this->authorize('create', [LearningEnvironment::class, $this->school]);
        $data = $this->validate(['environmentName' => ['required', 'string', 'max:255'], 'section' => ['nullable', 'string', 'max:100'], 'subjectId' => ['required', Rule::exists('subjects', 'id')->where('school_id', $this->school->id)]]);
        LearningEnvironment::query()->create(['school_id' => $this->school->id, 'subject_id' => $data['subjectId'], 'name' => $data['environmentName'], 'section' => $data['section'] ?: null]);
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
        return Auth::user();
    }

    public function render()
    {
        return view('livewire.academic.manage', ['subjects' => $this->school->subjects()->with('learningEnvironments:id,subject_id,name,section')->orderBy('name')->get()]);
    }
}
