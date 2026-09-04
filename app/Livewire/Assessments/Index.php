<?php

namespace App\Livewire\Assessments;

use App\Livewire\Concerns\ManagesLearningContent;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningEnvironment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Room Assessments')]
class Index extends Component
{
    use ManagesLearningContent;

    public LearningEnvironment $learningEnvironment;

    public string $title = '';

    public string $instructions = '';

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('viewLearningContent', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function create(): void
    {
        $this->authorize('create', [Assessment::class, $this->learningEnvironment]);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
        ]);

        $assessment = $this->learningEnvironment->assessments()->create([
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?: null,
            'status' => 'draft',
        ]);

        $this->reset('title', 'instructions');
        Flux::toast(variant: 'success', text: 'Assessment created. You can now add questions.');

        $this->redirect(route('assessments.edit', $assessment), navigate: true);
    }

    public function publish(int $assessmentId): void
    {
        $assessment = $this->learningEnvironment->assessments()->withCount('questions')->findOrFail($assessmentId);
        $this->authorize('update', $assessment);

        if ($assessment->questions_count === 0) {
            Flux::toast(variant: 'danger', text: 'An assessment needs at least one question before publishing.');

            return;
        }

        $assessment->update(['status' => 'published', 'published_at' => now()]);
        Flux::toast(variant: 'success', text: 'Assessment published successfully.');
    }

    public function unpublish(int $assessmentId): void
    {
        $assessment = $this->learningEnvironment->assessments()->findOrFail($assessmentId);
        $this->authorize('update', $assessment);

        $assessment->update(['status' => 'draft', 'published_at' => null]);
        Flux::toast(variant: 'info', text: 'Assessment unpublished and reverted to draft.');
    }

    public function delete(int $assessmentId): void
    {
        $assessment = $this->learningEnvironment->assessments()->findOrFail($assessmentId);
        $this->authorize('delete', $assessment);

        $assessment->delete();
        Flux::toast(variant: 'success', text: 'Assessment deleted.');
    }

    public function render(): View
    {
        $assessments = $this->learningEnvironment->assessments()
            ->with(['author:id,name'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->when(! $this->canManage(), fn ($query) => $query->published())
            ->get();

        return view('livewire.assessments.index', [
            'assessments' => $assessments,
            'attempts' => AssessmentAttempt::query()
                ->whereBelongsTo(Auth::user(), 'student')
                ->whereIn('assessment_id', $assessments->modelKeys())
                ->get()
                ->keyBy('assessment_id'),
        ]);
    }
}
