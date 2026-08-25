<section class="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $assessment->title }}</flux:heading>
            <flux:text>{{ __('Your Assessment Results') }}</flux:text>
        </div>
        <flux:button :href="route('learning-environments.assessments', $assessment->learning_environment_id)"
            wire:navigate variant="subtle">
            ← {{ __('Back to Assessments') }}
        </flux:button>
    </div>

    <!-- OVERVIEW CARD -->
    <flux:card class="flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
            <div>
                <flux:subheading>{{ __('Final Score') }}</flux:subheading>
                <flux:heading size="xl" class="text-teal-600 dark:text-teal-400">
                    {{ $attempt->score }}%
                </flux:heading>
            </div>
            <div class="text-right">
                <flux:badge :variant="$attempt->evaluation_status === 'evaluated' ? 'teal' : 'zinc'">
                    {{ str($attempt->evaluation_status)->headline() }}
                </flux:badge>
                <flux:text class="mt-1 text-xs">
                    {{ __('Submitted: :date', ['date' => $attempt->submitted_at?->format('M j, Y g:i A')]) }}
                </flux:text>
            </div>
        </div>

        @if ($attempt->feedback)
            <div class="rounded-md bg-zinc-50 p-4 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('Teacher Feedback:') }}</flux:heading>
                <flux:text class="mt-1 italic">{{ $attempt->feedback }}</flux:text>
                @if ($attempt->evaluator)
                    <flux:text class="mt-2 text-xs text-zinc-500">
                        — {{ $attempt->evaluator->name }}, {{ $attempt->evaluated_at?->format('M j, Y') }}
                    </flux:text>
                @endif
            </div>
        @endif
    </flux:card>

    <!-- DETAILED RESPONSES REVIEW -->
    <flux:heading size="lg">{{ __('Question Breakdown') }}</flux:heading>

    <div class="flex flex-col gap-4">
        @foreach ($attempt->responses as $index => $response)
            <flux:card wire:key="response-{{ $response->id }}" class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-4">
                    <flux:heading size="md">
                        {{ $index + 1 }}. {{ $response->assessmentQuestion->prompt }}
                    </flux:heading>

                    @if ($response->is_correct)
                        <flux:badge variant="teal" size="sm">✓ {{ __('Correct') }}</flux:badge>
                    @else
                        <flux:badge variant="danger" size="sm">✕ {{ __('Incorrect') }}</flux:badge>
                    @endif
                </div>

                <div class="rounded-md border border-zinc-200 p-3 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900">
                    <flux:text size="sm" class="font-medium">{{ __('Your Response:') }}</flux:text>
                    <flux:text size="sm"
                        class="{{ $response->is_correct ? 'text-teal-600 dark:text-teal-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $response->response ?? __('No response provided') }}
                    </flux:text>
                </div>
            </flux:card>
        @endforeach
    </div>
</section>
