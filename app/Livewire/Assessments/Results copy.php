<?php

namespace App\Livewire\Assessments;

use App\Actions\Performance\RecordPerformance;
use App\Models\Assessment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Assessment results')]
class Results extends Component
{
    public Assessment $assessment;

    /** @var array<int, string> */
    public array $scores = [];

    /** @var array<int, string> */
    public array $feedback = [];

    public function mount(Assessment $assessment): void
    {
        $this->authorize('viewResults', $assessment);
        $this->assessment = $assessment;

        foreach ($assessment->attempts as $attempt) {
            $this->scores[$attempt->id] = (string) ($attempt->score ?? '');
            $this->feedback[$attempt->id] = $attempt->feedback ?? '';
        }
    }

    public function evaluate(int $attemptId): void
    {
        $attempt = $this->assessment->attempts()->findOrFail($attemptId);
        $this->authorize('evaluate', $attempt);

        $data = $this->validate([
            "scores.{$attempt->id}" => ['required', 'numeric', 'min:0', 'max:100'],
            "feedback.{$attempt->id}" => ['nullable', 'string'],
        ]);

        $attempt->update([
            'score' => $data['scores'][$attempt->id],
            'feedback' => $data['feedback'][$attempt->id] ?? null,
            'evaluation_status' => 'evaluated',
            'evaluated_by' => Auth::id(),
            'evaluated_at' => now(),
        ]);

        (new RecordPerformance)->handle($attempt->refresh());
        Flux::toast(variant: 'success', text: 'Assessment evaluated and performance updated.');
    }

    public function render(): View
    {
        return view('livewire.assessments.results', [
            'attempts' => $this->assessment->attempts()->with('student:id,name,email')->latest('submitted_at')->get(),
        ]);
    }
}
