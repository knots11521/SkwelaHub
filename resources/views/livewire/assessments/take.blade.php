<section class="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $assessment->title }}</flux:heading>
            <flux:text>{{ __('Read carefully and select your answers before submitting.') }}</flux:text>
        </div>
        <flux:button :href="route('learning-environments.assessments', $assessment->learning_environment_id)"
            wire:navigate variant="subtle">
            ← {{ __('Cancel') }}
        </flux:button>
    </div>

    @if ($assessment->instructions)
        <flux:card class="bg-zinc-50 dark:bg-zinc-900">
            <flux:heading size="sm">{{ __('Instructions:') }}</flux:heading>
            <flux:text class="mt-1 whitespace-pre-line">{{ $assessment->instructions }}</flux:text>
        </flux:card>
    @endif

    <form wire:submit="submit" class="flex flex-col gap-6">
        @foreach ($assessment->questions as $qIndex => $question)
            <flux:card wire:key="question-item-{{ $question->id }}" class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <flux:heading size="md">{{ $qIndex + 1 }}. {{ $question->prompt }}</flux:heading>

                    <!-- Instant Selection Feedback -->
                    @if (isset($answers[$question->id]))
                        <flux:badge variant="teal" size="sm">{{ __('Selected') }}</flux:badge>
                    @else
                        <flux:badge variant="zinc" size="sm">{{ __('Unanswered') }}</flux:badge>
                    @endif
                </div>

                <flux:radio.group wire:model.live="answers.{{ $question->id }}">
                    @foreach ($question->options as $index => $option)
                        <flux:radio :value="$index" :label="$option" />
                    @endforeach
                </flux:radio.group>
            </flux:card>
        @endforeach

        <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ count($answers) }} / {{ $assessment->questions->count() }} {{ __('Questions answered') }}
            </flux:text>
            <flux:button type="submit" variant="primary" size="base">
                {{ __('Submit Assessment') }}
            </flux:button>
        </div>
    </form>
</section>
