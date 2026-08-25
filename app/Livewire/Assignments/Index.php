<?php

namespace App\Livewire\Assignments;

use App\Actions\Gamification\AwardGamification;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\User;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Assignments')]
class Index extends Component
{
    public LearningEnvironment $learningEnvironment;

    public string $title = '';

    public string $instructions = '';

    public string $dueAt = '';

    /** @var array<int, string> */
    public array $submissionContents = [];

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('view', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function create(): void
    {
        $this->authorize('create', [Assignment::class, $this->learningEnvironment]);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'dueAt' => ['nullable', 'date'],
        ]);

        $this->learningEnvironment->assignments()->create([
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?: null,
            'due_at' => $data['dueAt'] ?: null,
        ]);

        $this->reset('title', 'instructions', 'dueAt');
        Flux::toast(variant: 'success', text: 'Assignment saved as a draft.');
    }

    public function publish(int $assignmentId): void
    {
        $assignment = $this->learningEnvironment->assignments()->findOrFail($assignmentId);
        $this->authorize('update', $assignment);

        $assignment->update(['status' => 'published', 'published_at' => now()]);
        Flux::toast(variant: 'success', text: 'Assignment published.');
    }

    public function submit(int $assignmentId): void
    {
        $assignment = $this->learningEnvironment->assignments()->published()->findOrFail($assignmentId);
        $this->authorize('create', [AssignmentSubmission::class, $assignment]);

        $this->validate([
            "submissionContents.{$assignment->id}" => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $attempt = (int) AssignmentSubmission::query()
            ->whereBelongsTo($assignment)
            ->whereBelongsTo($user, 'student')
            ->max('attempt') + 1;

        $submission = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'learning_environment_id' => $this->learningEnvironment->id,
            'student_id' => $user->id,
            'attempt' => $attempt,
            'content' => $this->submissionContents[$assignment->id],
            'submitted_at' => now(),
        ]);

        (new AwardGamification)->handle($submission, 10, 'Assignment completed');

        unset($this->submissionContents[$assignment->id]);
        Flux::toast(variant: 'success', text: 'Work submitted.');
    }

    public function canManage(): bool
    {
        return Auth::user()->hasLearningEnvironmentRole($this->learningEnvironment, SchoolRole::Teacher);
    }

    public function render(): View
    {
        $assignments = $this->learningEnvironment->assignments()
            ->with('author:id,name')
            ->latest()
            ->when(! $this->canManage(), fn ($query) => $query->published())
            ->get();

        return view('livewire.assignments.index', [
            'assignments' => $assignments,
            'submissions' => AssignmentSubmission::query()
                ->whereBelongsTo(Auth::user(), 'student')
                ->whereIn('assignment_id', $assignments->modelKeys())
                ->latest('attempt')
                ->get()
                ->keyBy('assignment_id'),
        ]);
    }
}
