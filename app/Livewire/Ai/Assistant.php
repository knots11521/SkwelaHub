<?php

namespace App\Livewire\Ai;

use App\Actions\Ai\GenerateSuggestion;
use App\Actions\Ai\PublishSuggestion;
use App\Models\AiSuggestion;
use App\Models\LearningEnvironment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Title('AI teacher assistant')]
class Assistant extends Component
{
    public LearningEnvironment $learningEnvironment;

    public string $kind = 'assignment';

    public string $prompt = '';

    /** @var array<int, string> */
    public array $titles = [];

    /** @var array<int, string> */
    public array $contents = [];

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('create', [AiSuggestion::class, $learningEnvironment]);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function generate(GenerateSuggestion $generateSuggestion): void
    {
        $data = $this->validate([
            'kind' => ['required', 'in:assignment,assessment,rubric,material'],
            'prompt' => ['required', 'string', 'max:3000'],
        ]);
        $this->authorize('create', [AiSuggestion::class, $this->learningEnvironment]);

        try {
            /** @var User $teacher */
            $teacher = Auth::user();
            $generateSuggestion->handle($teacher, $this->learningEnvironment, $data['kind'], $data['prompt']);
        } catch (RuntimeException $exception) {
            $this->addError('prompt', $exception->getMessage());

            return;
        }

        $this->reset('prompt');
        Flux::toast(variant: 'success', text: 'AI suggestion saved as a draft for your review.');
    }

    public function saveReview(int $suggestionId): void
    {
        $suggestion = $this->learningEnvironment->aiSuggestions()->findOrFail($suggestionId);
        $this->authorize('update', $suggestion);
        $data = $this->validate([
            "titles.{$suggestion->id}" => ['required', 'string', 'max:255'],
            "contents.{$suggestion->id}" => ['required', 'string'],
        ]);

        $suggestion->update([
            'title' => $data['titles'][$suggestion->id],
            'content' => $data['contents'][$suggestion->id],
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);
        Flux::toast(variant: 'success', text: 'Teacher review saved.');
    }

    public function approve(int $suggestionId): void
    {
        $suggestion = $this->learningEnvironment->aiSuggestions()->findOrFail($suggestionId);
        $this->authorize('approve', $suggestion);

        $suggestion->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        Flux::toast(variant: 'success', text: 'Suggestion approved. Publish remains a separate teacher action.');
    }

    public function publish(int $suggestionId, PublishSuggestion $publishSuggestion): void
    {
        $suggestion = $this->learningEnvironment->aiSuggestions()->findOrFail($suggestionId);
        $this->authorize('publish', $suggestion);

        /** @var User $teacher */
        $teacher = Auth::user();
        $publishSuggestion->handle($teacher, $suggestion);

        Flux::toast(variant: 'success', text: 'Suggestion published through the teacher-controlled workflow.');
    }

    public function render(): View
    {
        $suggestions = $this->learningEnvironment->aiSuggestions()
            ->whereBelongsTo(Auth::user(), 'requester')
            ->latest()
            ->get();

        foreach ($suggestions->where('status', 'draft') as $suggestion) {
            $this->titles[$suggestion->id] ??= $suggestion->title ?? '';
            $this->contents[$suggestion->id] ??= $suggestion->content ?? '';
        }

        return view('livewire.ai.assistant', ['suggestions' => $suggestions]);
    }
}
