<section class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <flux:heading size="xl">{{ $isTeacher ? __('Assessments Management Hub') : __('My Assessments Hub') }}
        </flux:heading>
        <flux:text>
            {{ $isTeacher ? __('Monitor assessment activity and review student submissions across your classes.') : __('View and complete assessments assigned to your enrolled classes.') }}
        </flux:text>
    </div>

    <flux:card class="flex flex-col gap-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3 px-2">{{ __('Assessment') }}</th>
                        <th class="py-3 px-2">{{ __('Classroom') }}</th>
                        <th class="py-3 px-2">{{ $isTeacher ? __('Questions') : __('Status') }}</th>
                        <th class="py-3 px-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($assessments as $assessment)
                        <tr wire:key="global-assessment-{{ $assessment->id }}">
                            <td class="py-3 px-2 font-medium">
                                {{ $assessment->title }}
                                <div class="text-xs text-zinc-500">
                                    {{ __('By :name', ['name' => $assessment->author->name ?? __('Unknown')]) }}
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <flux:badge variant="zinc">{{ $assessment->learningEnvironment->name }}</flux:badge>
                            </td>
                            <td class="py-3 px-2">
                                @if ($isTeacher)
                                    <span
                                        class="text-xs text-zinc-500">{{ __(':count Qs', ['count' => $assessment->questions_count]) }}</span>
                                @else
                                    <flux:badge :variant="$assessment->status === 'published' ? 'teal' : 'zinc'">
                                        {{ str($assessment->status)->headline() }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-right">
                                @if ($isTeacher)
                                    <flux:button :href="route('assessments.results', $assessment)" wire:navigate
                                        size="sm" variant="subtle">
                                        {{ __('Results (:count)', ['count' => $assessment->attempts_count]) }}
                                    </flux:button>
                                @else
                                    @if ($attempts->has($assessment->id))
                                        <flux:button :href="route('assessments.results', $assessment)" wire:navigate
                                            size="sm" variant="subtle">
                                            {{ __('View Result') }}
                                        </flux:button>
                                    @else
                                        <flux:button :href="route('assessments.take', $assessment)" wire:navigate
                                            size="sm" variant="primary">
                                            {{ __('Take Assessment') }}
                                        </flux:button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-zinc-500">
                                {{ $isTeacher ? __('No assessments have been created in your classes.') : __('No assessments available across your enrolled classes.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $assessments->links() }}
    </flux:card>
</section>
