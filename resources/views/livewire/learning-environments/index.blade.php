<section class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <flux:heading size="xl">
            {{ $isTeacher ? __('My Teaching Environments') : __('My Learning Environments') }}
        </flux:heading>
        <flux:text>
            {{ $isTeacher ? __('Access and manage your active teaching classrooms.') : __('Access and view your enrolled subjects and classrooms.') }}
        </flux:text>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        @forelse($environments as $environment)
            <flux:card wire:key="environment-{{ $environment->id }}"
                class="flex flex-col justify-between space-y-5 border border-zinc-200/80 dark:border-zinc-800 hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <flux:badge size="sm" color="zinc" variant="pill">
                            {{ $environment->subject->name }}
                        </flux:badge>
                        <span class="text-xs text-zinc-400 font-medium">{{ $environment->school->name }}</span>
                    </div>
                    <div>
                        <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">
                            {{ $environment->name }}{{ $environment->section ? ' · ' . $environment->section : '' }}
                        </flux:heading>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800/80">
                    <flux:button :href="route('learning-environments.materials', $environment)" wire:navigate
                        variant="primary" size="sm" icon="book-open">
                        {{ __('Materials') }}
                    </flux:button>
                    <flux:button :href="route('learning-environments.assignments', $environment)" wire:navigate
                        size="sm" icon="document-text">
                        {{ __('Assignments') }}
                    </flux:button>
                    <flux:button :href="route('learning-environments.assessments', $environment)" wire:navigate
                        size="sm" icon="clipboard-document-check">
                        {{ __('Assessments') }}
                    </flux:button>

                    @if ($isTeacher)
                        <flux:button :href="route('learning-environments.ai-assistance', $environment)" wire:navigate
                            size="sm" icon="sparkles"
                            class="bg-gradient-to-r from-purple-500/10 to-indigo-500/10 border-purple-200/50 dark:border-purple-800/50 text-purple-700 dark:text-purple-300">
                            {{ __('AI Assistant') }}
                        </flux:button>
                    @endif

                    @if ($isStudent)
                        <flux:button :href="route('performance.index')" wire:navigate size="sm" icon="chart-bar">
                            {{ __('My Performance') }}
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card
                class="md:col-span-2 border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center py-12 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mb-3">
                    <flux:icon icon="academic-cap" class="size-6" />
                </div>
                <flux:heading size="md" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ __('No environments assigned') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-500 max-w-sm mt-1">
                    {{ __('You have not been assigned or enrolled in a learning environment yet. Reach out to your administrator to get started.') }}
                </flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
