<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Assessment')]
class Edit extends Component
{
    public Assessment $assessment;
    public string $title = '';
    public string $instructions = '';
    public array $questions = [];

    public function mount(Assessment $assessment): void
    {
        $this->authorize('update', $assessment);
        $this->assessment = $assessment->load('questions');
        $this->title = $assessment->title;
        $this->instructions = $assessment->instructions ?? '';

        foreach ($assessment->questions as $question) {
            $this->questions[] = [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'options' => $question->options,
                'correct_option' => $question->correct_option,
            ];
        }

        if (empty($this->questions)) {
            $this->addQuestion();
        }
    }

    public function addQuestion(): void
    {
        $this->questions[] = [
            'id' => null,
            'prompt' => '',
            'options' => ['', ''],
            'correct_option' => 0,
        ];
    }

    public function removeQuestion(int $index): void
    {
        if (count($this->questions) > 1) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
    }

    public function addOption(int $qIndex): void
    {
        $this->questions[$qIndex]['options'][] = '';
    }

    public function removeOption(int $qIndex, int $oIndex): void
    {
        if (count($this->questions[$qIndex]['options']) > 2) {
            unset($this->questions[$qIndex]['options'][$oIndex]);
            $this->questions[$qIndex]['options'] = array_values($this->questions[$qIndex]['options']);

            if ($this->questions[$qIndex]['correct_option'] >= count($this->questions[$qIndex]['options'])) {
                $this->questions[$qIndex]['correct_option'] = 0;
            }
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->assessment);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*' => ['required', 'string'],
            'questions.*.correct_option' => ['required', 'integer', 'min:0'],
        ]);

        $this->assessment->update([
            'title' => $this->title,
            'instructions' => $this->instructions ?: null,
        ]);

        $existingIds = collect($this->questions)->pluck('id')->filter()->toArray();
        $this->assessment->questions()->whereNotIn('id', $existingIds)->delete();

        foreach ($this->questions as $q) {
            $this->assessment->questions()->updateOrCreate(
                ['id' => $q['id']],
                [
                    'prompt' => $q['prompt'],
                    'options' => $q['options'],
                    'correct_option' => (int) $q['correct_option'],
                ]
            );
        }

        Flux::toast(variant: 'success', text: 'Assessment questions saved.');
        $this->redirect(route('learning-environments.assessments', $this->assessment->learning_environment_id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.assessments.edit');
    }
}
