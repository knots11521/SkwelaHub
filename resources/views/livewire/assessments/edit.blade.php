<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Edit Assessment') }}: {{ $assessment->title }}</flux:heading>
            <flux:text>{{ __('Build, review, or modify your assessment questions and choices.') }}</flux:text>
        </div>
        <flux:button :href="route('learning-environments.assessments', $assessment->learning_environment_id)" wire:navigate variant="subtle">
            ← {{ __('Back to Room') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Assessment Details') }}</flux:heading>

            <flux:input wire:model="title" :label="__('Title')" required />
            <flux:textarea wire:model="instructions" :label="__('Instructions')" />

            <hr class="border-zinc-200 dark:border-zinc-700" />

            <flux:heading size="lg">{{ __('Questions Builder') }}</flux:heading>

            <div class="flex flex-col gap-6">
                @foreach ($questions as $qIndex => $q)
                    <div wire:key="question-builder-{{ $qIndex }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">{{ __('Question') }} #{{ $qIndex + 1 }}</flux:heading>
                            @if(count($questions) > 1)
                                <flux:button type="button" wire:click="removeQuestion({{ $qIndex }})" variant="danger" size="sm">
                                    {{ __('Remove Question') }}
                                </flux:button>
                            @endif
                        </div>

                        <flux:input wire:model="questions.{{ $qIndex }}.prompt" :placeholder="__('Enter question prompt...')" required />

                        <!-- Dynamic Choice Builder -->
                        <div class="flex flex-col gap-3 pl-2 border-l-2 border-zinc-200 dark:border-zinc-700">
                            <flux:subheading>{{ __('Options (Select radio button to indicate correct answer):') }}</flux:subheading>

                            @foreach ($q['options'] as $oIndex => $option)
                                <div wire:key="q-{{ $qIndex }}-o-{{ $oIndex }}" class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="correct_option_{{ $qIndex }}"
                                        wire:model="questions.{{ $qIndex }}.correct_option"
                                        value="{{ $oIndex }}"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <div class="flex-1">
                                        <flux:input wire:model="questions.{{ $qIndex }}.options.{{ $oIndex }}" :placeholder="__('Option text')" required />
                                    </div>
                                    @if(count($q['options']) > 2)
                                        <flux:button type="button" wire:click="removeOption({{ $qIndex }}, {{ $oIndex }})" variant="subtle" size="sm">✕</flux:button>
                                    @endif
                                </div>
                            @endforeach

                            <div class="mt-2">
                                <flux:button type="button" wire:click="addOption({{ $qIndex }})" variant="subtle" size="sm">
                                    + {{ __('Add Choice') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center mt-4">
                <flux:button type="button" wire:click="addQuestion" variant="filled">
                    + {{ __('Add Question') }}
                </flux:button>

                <flux:button type="submit" variant="primary">
                    {{ __('Save Assessment') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</x-page-section>
