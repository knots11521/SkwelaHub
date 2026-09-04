<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><flux:heading size="xl">{{ $assignment->title }}</flux:heading><flux:text>{{ __('Student submissions') }}</flux:text></div>
        <flux:button :href="route('learning-environments.assignments', $assignment->learningEnvironment)" wire:navigate>{{ __('Back to assignments') }}</flux:button>
    </div>

    <div class="flex flex-col gap-4">
        @forelse($submissions as $submission)
            <flux:card wire:key="submission-{{ $submission->id }}">
                <div class="flex flex-col gap-1"><flux:heading>{{ $submission->student->name }}</flux:heading><flux:text>{{ __('Attempt :attempt · Submitted :date', ['attempt' => $submission->attempt, 'date' => $submission->submitted_at?->format('M j, Y g:i A')]) }}</flux:text></div>
                <div class="mt-4 whitespace-pre-line">{{ $submission->content }}</div>
                <form wire:submit="evaluate({{ $submission->id }})" class="mt-5 grid gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700 md:grid-cols-2">
                    <flux:input wire:model="scores.{{ $submission->id }}" type="number" min="0" max="100" step="0.01" :label="__('Score (0–100)')" required />
                    <flux:textarea wire:model="feedback.{{ $submission->id }}" :label="__('Feedback')" class="md:col-span-2" />
                    <div class="flex items-center gap-3 md:col-span-2"><flux:badge>{{ str($submission->evaluation_status)->headline() }}</flux:badge>@if($submission->evaluated_at)<flux:text>{{ __('Evaluated :date', ['date' => $submission->evaluated_at->format('M j, Y g:i A')]) }}</flux:text>@endif<flux:button type="submit" variant="primary">{{ __('Save evaluation') }}</flux:button></div>
                </form>
            </flux:card>
        @empty
            <flux:card><flux:text>{{ __('No work has been submitted yet.') }}</flux:text></flux:card>
        @endforelse
    </div>
</x-page-section>
