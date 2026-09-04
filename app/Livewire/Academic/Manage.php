<?php

namespace App\Livewire\Academic;

use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Academic structure')]
class Manage extends Component
{
    public School $school;

    public string $subjectSearch = '';

    public function mount(School $school)
    {
        $this->authorize('view', $school);
        $this->school = $school;

        if ($this->user()->hasRole(SchoolRole::Teacher->value)) {
            $this->redirectRoute('learning-environments.index', navigate: true);
        }
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

    #[Computed]
    public function subjects(): LengthAwarePaginator
    {
        $query = $this->school->subjects()
            ->with('learningEnvironments:id,subject_id,created_by,name,section')
            ->orderBy('name');

        if ($this->subjectSearch !== '') {
            $query->where('name', 'like', '%'.$this->subjectSearch.'%');
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.academic.manage');
    }
}
