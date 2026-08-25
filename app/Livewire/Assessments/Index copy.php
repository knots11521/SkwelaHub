<?php

namespace App\Livewire\Assessments;

use App\Actions\Gamification\AwardGamification;
use App\Actions\Performance\RecordPerformance;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningEnvironment;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Assessments')]
class Index extends Component
{
    public LearningEnvironment $learningEnvironment;

    public string $title = '';

    public string $instructions = '';

    public string $questionPrompt = '';

    public string $questionOptions = '';

    public int $correctOption = 1;

    /** @var array<int, int|string> */
    public array $answers = [];

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('view', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function create(): void
    {
        $this->authorize('create', [Assessment::class, $this->learningEnvironment]);
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'questionPrompt' => ['required', 'string'],
            'questionOptions' => ['required', 'string'],
            'correctOption' => ['required', 'integer', 'min:1'],
        ]);

        $options = array_values(array_filter(preg_split('/\r\n|\r|\n/', trim($data['questionOptions']))));
        if (count($options) < 2 || $data['correctOption'] > count($options)) {
            throw ValidationException::withMessages(['questionOptions' => 'Provide at least two options and a valid correct option number.']);
        }

        $assessment = $this->learningEnvironment->assessments()->create([
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?: null,
        ]);
        $assessment->questions()->create([
            'prompt' => $data['questionPrompt'],
            'options' => $options,
            'correct_option' => $data['correctOption'] - 1,
        ]);

        $this->reset('title', 'instructions', 'questionPrompt', 'questionOptions', 'correctOption');
        $this->correctOption = 1;
        Flux::toast(variant: 'success', text: 'Assessment saved as a draft.');
    }

    public function publish(int $assessmentId): void
    {
        $assessment = $this->learningEnvironment->assessments()->withCount('questions')->findOrFail($assessmentId);
        $this->authorize('update', $assessment);

        if ($assessment->questions_count === 0) {
            throw ValidationException::withMessages(['title' => 'An assessment needs at least one question before publishing.']);
        }

        $assessment->update(['status' => 'published', 'published_at' => now()]);
        Flux::toast(variant: 'success', text: 'Assessment published.');
    }

    public function submit(int $assessmentId): void
    {
        $assessment = $this->learningEnvironment->assessments()->published()->with('questions')->findOrFail($assessmentId);
        $this->authorize('create', [AssessmentAttempt::class, $assessment]);

        if ($assessment->attempts()->whereBelongsTo(Auth::user(), 'student')->exists()) {
            Flux::toast(text: 'You have already completed this assessment.');

            return;
        }

        foreach ($assessment->questions as $question) {
            $this->validate(["answers.{$question->id}" => ['required', 'integer', 'min:0', 'max:'.(count($question->options) - 1)]]);
        }

        $correctAnswers = 0;
        foreach ($assessment->questions as $question) {
            if ((int) $this->answers[$question->id] === $question->correct_option) {
                $correctAnswers++;
            }
        }

        $attempt = DB::transaction(function () use ($assessment, $correctAnswers): AssessmentAttempt {
            $attempt = $assessment->attempts()->create([
                'learning_environment_id' => $this->learningEnvironment->id,
                'student_id' => Auth::id(),
                'attempt' => 1,
                'score' => round(($correctAnswers / $assessment->questions->count()) * 100, 2),
                'submitted_at' => now(),
                'result_available_at' => now(),
            ]);

            foreach ($assessment->questions as $question) {
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

        $this->answers = [];
        Flux::toast(variant: 'success', text: 'Assessment completed. Your result is available now.');
    }

    public function canManage(): bool
    {
        return Auth::user()->hasLearningEnvironmentRole($this->learningEnvironment, SchoolRole::Teacher);
    }

    public function render(): View
    {
        $assessments = $this->learningEnvironment->assessments()
            ->with(['author:id,name', 'questions'])
            ->withCount('attempts')
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
