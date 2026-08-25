<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Page Header --}}
    <div class="pb-6 border-b border-zinc-200 dark:border-zinc-800 space-y-1">
        <div class="flex items-center gap-2">
            <span
                class="text-xs font-semibold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase">{{ __('Overview') }}</span>
        </div>
        <flux:heading size="xl" class="font-extrabold tracking-tight">
            {{ __('My Performance') }}
        </flux:heading>
        <flux:text class="text-zinc-500 max-w-2xl">
            {{ __('Academic results are derived from assessed work. Engagement points are tracked separately.') }}
        </flux:text>
    </div>

    {{-- Top Summary Stats --}}
    <div class="grid gap-4 sm:grid-cols-2">
        {{-- Academic Records Stat --}}
        <flux:card
            class="p-6 border border-zinc-200/80 dark:border-zinc-800 bg-gradient-to-br from-white to-zinc-50/50 dark:from-zinc-900 dark:to-zinc-900/50 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:text size="sm" class="text-zinc-500 font-medium">{{ __('Academic Records') }}</flux:text>
                    <div class="text-3xl font-black text-zinc-900 dark:text-white">
                        {{ $performanceRecords->count() }}
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <flux:icon icon="academic-cap" class="size-6" />
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 text-xs text-zinc-500">
                {{ trans_choice(':count assessed activity|:count assessed activities', $performanceRecords->count(), ['count' => $performanceRecords->count()]) }}
            </div>
        </flux:card>

        {{-- Engagement Points Stat --}}
        <flux:card
            class="p-6 border border-zinc-200/80 dark:border-zinc-800 bg-gradient-to-br from-white to-zinc-50/50 dark:from-zinc-900 dark:to-zinc-900/50 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:text size="sm" class="text-zinc-500 font-medium">{{ __('Engagement Points') }}
                    </flux:text>
                    <div class="text-3xl font-black text-amber-500 dark:text-amber-400">
                        {{ number_format($gamificationEvents->sum('points')) }}
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-500 dark:text-amber-400">
                    <flux:icon icon="sparkles" class="size-6" />
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 text-xs text-zinc-500">
                {{ __('Earned across all completed learning activities') }}
            </div>
        </flux:card>
    </div>

    {{-- Main Columns --}}
    <div class="grid gap-8 lg:grid-cols-2 items-start">

        {{-- Academic Performance Column --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg" class="font-bold">
                    {{ __('Academic Performance') }}
                </flux:heading>
                <flux:badge size="sm" variant="pill" color="indigo">
                    {{ $performanceRecords->count() }}
                </flux:badge>
            </div>

            <div class="space-y-3">
                @forelse($performanceRecords as $record)
                    @php
                        $score = $record->score;
                        $scoreColor = match (true) {
                            $score >= 90 => 'emerald',
                            $score >= 75 => 'indigo',
                            $score >= 60 => 'amber',
                            default => 'rose',
                        };
                    @endphp
                    <flux:card wire:key="performance-{{ $record->id }}"
                        class="p-5 border border-zinc-200/80 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-200">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <span
                                    class="text-[10px] font-bold tracking-wider uppercase text-zinc-400 dark:text-zinc-500">
                                    {{ class_basename($record->source_type) }}
                                </span>
                                <flux:heading size="md" class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $record->learningEnvironment->name }}
                                </flux:heading>
                                @if ($record->learningEnvironment->section)
                                    <flux:text size="sm" class="text-zinc-500">
                                        {{ __('Section: :section', ['section' => $record->learningEnvironment->section]) }}
                                    </flux:text>
                                @endif
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-2xl font-black text-zinc-900 dark:text-white">
                                    {{ __(':score%', ['score' => $record->score]) }}
                                </div>
                                <flux:badge size="sm" variant="pill" :color="$scoreColor" class="mt-1">
                                    {{ $score >= 75 ? __('Passed') : __('Needs Work') }}
                                </flux:badge>
                            </div>
                        </div>

                        {{-- Progress Bar Representation --}}
                        <div class="mt-4 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 bg-indigo-600 dark:bg-indigo-500"
                                style="width: {{ min(100, max(0, $record->score)) }}%"></div>
                        </div>
                    </flux:card>
                @empty
                    <flux:card
                        class="border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center py-10 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                        <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mb-2">
                            <flux:icon icon="document-text" class="size-6" />
                        </div>
                        <flux:heading size="md" class="font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ __('No evaluations yet') }}
                        </flux:heading>
                        <flux:text class="text-xs text-zinc-500 max-w-xs mt-1">
                            {{ __('Evaluated coursework and activity scores will automatically appear here.') }}
                        </flux:text>
                    </flux:card>
                @endforelse
            </div>
        </div>

        {{-- Achievements & Activity Points Column --}}
        <div class="space-y-6">
            {{-- Achievements Section --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg" class="font-bold">
                        {{ __('Achievements') }}
                    </flux:heading>
                    <flux:badge size="sm" variant="pill" color="amber">
                        {{ $achievements->count() }}
                    </flux:badge>
                </div>

                <div class="space-y-3">
                    @forelse($achievements as $userAchievement)
                        <flux:card wire:key="achievement-{{ $userAchievement->id }}"
                            class="p-4 border border-zinc-200/80 dark:border-zinc-800 flex items-start gap-4 bg-amber-500/5 dark:bg-amber-500/10">
                            <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">
                                <flux:icon icon="trophy" class="size-5" />
                            </div>
                            <div class="space-y-0.5">
                                <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">
                                    {{ $userAchievement->achievement->name }}
                                </flux:heading>
                                <flux:text size="sm" class="text-zinc-500">
                                    {{ $userAchievement->achievement->description }}
                                </flux:text>
                            </div>
                        </flux:card>
                    @empty
                        <flux:card
                            class="border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center py-8 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                            <div class="p-2.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mb-2">
                                <flux:icon icon="trophy" class="size-5" />
                            </div>
                            <flux:text class="text-xs text-zinc-500">
                                {{ __('Complete a learning activity to earn your first achievement.') }}
                            </flux:text>
                        </flux:card>
                    @endforelse
                </div>
            </div>

            {{-- Activity Log Section --}}
            @if ($gamificationEvents->isNotEmpty())
                <div class="space-y-3 pt-2">
                    <flux:heading size="sm" class="font-bold uppercase tracking-wider text-xs text-zinc-400">
                        {{ __('Recent Activity Log') }}
                    </flux:heading>

                    <div class="space-y-2">
                        @foreach ($gamificationEvents as $event)
                            <flux:card wire:key="event-{{ $event->id }}"
                                class="p-3 border border-zinc-200/60 dark:border-zinc-800 flex items-center justify-between text-xs">
                                <div class="space-y-0.5 pr-2">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $event->reason }}
                                    </div>
                                    <div class="text-zinc-400">
                                        {{ $event->learningEnvironment->name }}
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md shrink-0">
                                    <flux:icon icon="plus" class="size-3" />
                                    {{ $event->points }} {{ __('pts') }}
                                </span>
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
