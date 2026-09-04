<?php

namespace App\Livewire\Assessments;

use App\Actions\Gamification\AwardGamification;
use App\Actions\Performance\RecordPerformance;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Take Assessment')]
class Take extends Component
{
    public Assessment $assessment;

    public array $answers = [];

    public function mount(Assessment $assessment): void
    {
        $this->assessment = $assessment->load('questions');
        $this->authorize('create', [AssessmentAttempt::class, $this->assessment]);
    }

    public function submit(): void
    {
        $this->authorize('create', [AssessmentAttempt::class, $this->assessment]);

        foreach ($this->assessment->questions as $question) {
            $this->validate([
                "answers.{$question->id}" => ['required', 'integer', 'min:0', 'max:'.(count($question->options) - 1)],
            ], [], ["answers.{$question->id}" => 'question selection']);
        }

        $correctAnswers = 0;
        foreach ($this->assessment->questions as $question) {
            if ((int) $this->answers[$question->id] === $question->correct_option) {
                $correctAnswers++;
            }
        }

        $attempt = DB::transaction(function () use ($correctAnswers): AssessmentAttempt {
            $nextAttempt = $this->assessment->attempts()
                ->whereBelongsTo(Auth::user(), 'student')
                ->max('attempt') + 1;

            $attempt = $this->assessment->attempts()->create([
                'learning_environment_id' => $this->assessment->learning_environment_id,
                'student_id' => Auth::id(),
                'attempt' => $nextAttempt,
                'score' => round(($correctAnswers / $this->assessment->questions->count()) * 100, 2),
                'submitted_at' => now(),
                'result_available_at' => now(),
            ]);

            foreach ($this->assessment->questions as $question) {
                $answer = (int) $this->answers[$question->id];
                $attempt->responses()->create([
                    'assessment_question_id' => $question->id,
                    'response' => $question->options[$answer],
                    'is_correct' => $answer === $question->correct_option,
                ]);
            }

            return $attempt;
        });

        (new RecordPerformance)->handle($attempt);
        (new AwardGamification)->handle($attempt, 10, 'Assessment completed');

        Flux::toast(variant: 'success', text: 'Assessment submitted successfully!');
        $this->redirect(route('learning-environments.assessments', $this->assessment->learning_environment_id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.assessments.take');
    }
}
