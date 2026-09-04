<x-page-section max-width="4xl">
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

    <flux:card class="flex flex-col items-center gap-4 py-10 text-center">
        <div class="p-3 rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
            <flux:icon icon="document-magnifying-glass" class="size-6" />
        </div>
        <div>
            <flux:heading size="md">{{ __('No attempt recorded yet') }}</flux:heading>
            <flux:text class="mt-1 max-w-md text-sm text-zinc-500">
                {{ __('You have not submitted an attempt for this assessment. Once you do, your result will appear here.') }}
            </flux:text>
        </div>
        <flux:button :href="route('assessments.take', $assessment)" wire:navigate variant="primary">
            {{ __('Take Assessment') }}
        </flux:button>
    </flux:card>
</x-page-section>
