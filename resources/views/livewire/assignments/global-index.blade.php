<x-page-section>
    <div>
        <flux:heading size="xl">{{ $isTeacher ? __('Assignments Overview') : __('My Assignments') }}</flux:heading>
        <flux:text>
            {{ $isTeacher ? __('Review assignments across all your classes.') : __('Track due dates, submissions, and scores across all enrolled classes.') }}
        </flux:text>
    </div>

    @forelse ($environments as $environment)
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                <div>
                    <flux:heading size="lg">{{ $environment->name }}</flux:heading>
                    @if ($environment->section)
                        <flux:text class="text-xs">{{ $environment->section }}</flux:text>
                    @endif
                </div>
                <flux:button :href="route('learning-environments.assignments', $environment)" wire:navigate
                    size="sm" variant="subtle">
                    {{ $isTeacher ? __('Manage Class Assignments') : __('View Class Assignments') }}
                </flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-zinc-200 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="py-2 px-2">{{ __('Assignment') }}</th>
                            <th class="py-2 px-2">{{ __('Due Date') }}</th>
                            <th class="py-2 px-2 text-right">{{ $isTeacher ? __('Status') : __('Score / Grade') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($environment->assignments as $assignment)
                            @php
                                $submission = $submissions->get($assignment->id);
                            @endphp
                            <tr wire:key="assignment-{{ $assignment->id }}">
                                <td class="py-3 px-2 font-medium">
                                    {{ $assignment->title }}
                                    @if ($assignment->instructions)
                                        <div class="text-xs text-zinc-500 line-clamp-1">
                                            {{ $assignment->instructions }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    @if ($assignment->due_at)
                                        <flux:badge size="sm"
                                            :variant="$assignment->due_at->isPast() ? 'rose' : 'zinc'">
                                            {{ $assignment->due_at->format('M d, Y h:i A') }}
                                        </flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">{{ __('No due date') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-right font-mono text-xs">
                                    @if ($isTeacher)
                                        <flux:badge :variant="$assignment->status === 'published' ? 'teal' : 'zinc'">
                                            {{ str($assignment->status)->headline() }}
                                        </flux:badge>
                                    @else
                                        @if ($submission)
                                            <flux:badge variant="teal">
                                                {{ $submission->grade ?? ($submission->score ?? __('Submitted')) }}
                                            </flux:badge>
                                        @else
                                            <span class="text-xs text-zinc-400">{{ __('Pending') }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-xs text-zinc-500">
                                    {{ $isTeacher ? __('You have not created any assignments for this class.') : __('No assignments posted in this class.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    @empty
        <flux:card>
            <p class="text-center text-zinc-500">
                {{ $isTeacher ? __('You are not assigned to any learning environments.') : __('You are not enrolled in any learning environments.') }}
            </p>
        </flux:card>
    @endforelse
</x-page-section>
