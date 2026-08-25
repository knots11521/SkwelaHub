<?php

namespace App\Livewire\Assignments;

use App\Actions\Performance\RecordPerformance;
use App\Models\Assignment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Assignment submissions')]
class Submissions extends Component
{
    public Assignment $assignment;

    /** @var array<int, string> */
    public array $scores = [];

    /** @var array<int, string> */
    public array $feedback = [];

    public function mount(Assignment $assignment): void
    {
        $this->authorize('viewSubmissions', $assignment);
        $this->assignment = $assignment;

        foreach ($assignment->submissions as $submission) {
            $this->scores[$submission->id] = (string) ($submission->score ?? '');
            $this->feedback[$submission->id] = $submission->feedback ?? '';
        }
    }

    public function evaluate(int $submissionId): void
    {
        $submission = $this->assignment->submissions()->findOrFail($submissionId);
        $this->authorize('evaluate', $submission);

        $data = $this->validate([
            "scores.{$submission->id}" => ['required', 'numeric', 'min:0', 'max:100'],
            "feedback.{$submission->id}" => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $data['scores'][$submission->id],
            'feedback' => $data['feedback'][$submission->id] ?? null,
            'evaluation_status' => 'evaluated',
            'evaluated_by' => Auth::id(),
            'evaluated_at' => now(),
        ]);

        (new RecordPerformance)->handle($submission->refresh());
        Flux::toast(variant: 'success', text: 'Submission evaluated and performance updated.');
    }

    public function render(): View
    {
        return view('livewire.assignments.submissions', [
            'submissions' => $this->assignment->submissions()->with('student:id,name,email')->latest('submitted_at')->get(),
        ]);
    }
}
