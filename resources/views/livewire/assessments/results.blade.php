<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><flux:heading size="xl">{{ $assessment->title }}</flux:heading><flux:text>{{ __('Assessment results') }}</flux:text></div><flux:button :href="route('learning-environments.assessments', $assessment->learningEnvironment)" wire:navigate>{{ __('Back to assessments') }}</flux:button></div>
    <flux:card><flux:heading>{{ __('Completed attempts: :count', ['count' => $attempts->count()]) }}</flux:heading><flux:text>{{ __('Average score: :score%', ['score' => number_format((float) $attempts->avg('score'), 2)]) }}</flux:text></flux:card>
    <div class="flex flex-col gap-4">
        @forelse($attempts as $attempt)
            <flux:card wire:key="attempt-{{ $attempt->id }}">
                <flux:heading>{{ $attempt->student->name }}</flux:heading>
                <flux:text>{{ __('Score: :score% · Submitted :date', ['score' => $attempt->score, 'date' => $attempt->submitted_at?->format('M j, Y g:i A')]) }}</flux:text>
                <form wire:submit="evaluate({{ $attempt->id }})" class="mt-5 grid gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700 md:grid-cols-2">
                    <flux:input wire:model="scores.{{ $attempt->id }}" type="number" min="0" max="100" step="0.01" :label="__('Score (0–100)')" required />
                    <flux:textarea wire:model="feedback.{{ $attempt->id }}" :label="__('Feedback')" class="md:col-span-2" />
                    <div class="flex items-center gap-3 md:col-span-2"><flux:badge>{{ str($attempt->evaluation_status)->headline() }}</flux:badge>@if($attempt->evaluated_at)<flux:text>{{ __('Evaluated :date', ['date' => $attempt->evaluated_at->format('M j, Y g:i A')]) }}</flux:text>@endif<flux:button type="submit" variant="primary">{{ __('Save evaluation') }}</flux:button></div>
                </form>
            </flux:card>
        @empty
            <flux:card><flux:text>{{ __('No student has completed this assessment yet.') }}</flux:text></flux:card>
        @endforelse
    </div>
</x-page-section>
