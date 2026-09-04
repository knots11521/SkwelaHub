<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $learningEnvironment->name }}</flux:heading>
            <flux:text>{{ __('Assessment Room') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('learning-environments.assignments', $learningEnvironment)" wire:navigate>
                {{ __('Assignments') }}
            </flux:button>
            <flux:button :href="route('learning-environments.materials', $learningEnvironment)" wire:navigate>
                {{ __('Materials') }}
            </flux:button>
        </div>
    </div>

    @if ($this->canManage())
        <!-- QUICK ASSESSMENT CREATOR -->
        <flux:card>
            <form wire:submit="create" class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Create New Assessment') }}</flux:heading>
                <flux:input wire:model="title" :label="__('Title')" placeholder="e.g. Midterm Quiz" required />
                <flux:textarea wire:model="instructions" :label="__('Instructions')"
                    placeholder="e.g. Complete within 30 minutes..." />
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">+ {{ __('Create & Build Questions') }}</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <!-- ASSESSMENT COLLECTION TABLE -->
    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Assessments') }}</flux:heading>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3 px-2">{{ __('Title') }}</th>
                        <th class="py-3 px-2">{{ __('Status') }}</th>
                        <th class="py-3 px-2">{{ __('Questions') }}</th>
                        <th class="py-3 px-2">{{ __('Attempts') }}</th>
                        <th class="py-3 px-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($assessments as $assessment)
                        <tr wire:key="assessment-row-{{ $assessment->id }}">
                            <td class="py-3 px-2 font-medium">
                                {{ $assessment->title }}
                                <div class="text-xs text-zinc-500">
                                    {{ __('By :name', ['name' => $assessment->author->name]) }}
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <flux:badge :variant="$assessment->status === 'published' ? 'teal' : 'zinc'">
                                    {{ str($assessment->status)->headline() }}
                                </flux:badge>
                            </td>
                            <td class="py-3 px-2">{{ $assessment->questions_count }}</td>
                            <td class="py-3 px-2">{{ $assessment->attempts_count }}</td>
                            <td class="py-3 px-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($this->canManage())
                                        <!-- TEACHER ACTIONS -->
                                        <flux:button :href="route('assessments.edit', $assessment)" wire:navigate
                                            size="sm" variant="subtle">
                                            {{ $assessment->status === 'draft' ? __('Edit / Review') : __('View Questions') }}
                                        </flux:button>

                                        @if ($assessment->status === 'draft')
                                            <flux:button wire:click="publish({{ $assessment->id }})" size="sm"
                                                variant="primary">
                                                {{ __('Publish') }}
                                            </flux:button>
                                        @else
                                            <flux:button wire:click="unpublish({{ $assessment->id }})" size="sm"
                                                variant="filled">
                                                {{ __('Unpublish') }}
                                            </flux:button>
                                        @endif

                                        <flux:button :href="route('assessments.results', $assessment)" wire:navigate
                                            size="sm" variant="subtle">
                                            {{ __('Results') }}
                                        </flux:button>

                                        <flux:button wire:click="delete({{ $assessment->id }})"
                                            wire:confirm="Are you sure you want to delete this assessment?"
                                            size="sm" variant="danger">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @else
                                        <!-- STUDENT ACTIONS -->
                                        @if ($attempts->has($assessment->id))
                                            <div class="flex items-center gap-2">
                                                <flux:badge variant="teal">
                                                    {{ __('Score: :score%', ['score' => $attempts[$assessment->id]->score]) }}
                                                </flux:badge>
                                                <flux:button :href="route('assessments.results', $assessment)"
                                                    wire:navigate size="sm" variant="subtle">
                                                    {{ __('View Result') }}
                                                </flux:button>
                                            </div>
                                        @else
                                            <flux:button :href="route('assessments.take', $assessment)" wire:navigate
                                                size="sm" variant="primary">
                                                {{ __('Take Assessment') }}
                                            </flux:button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-zinc-500">
                                {{ $this->canManage() ? __('No assessments built yet.') : __('No published assessments available.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>
</x-page-section>
