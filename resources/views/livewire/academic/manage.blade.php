<x-page-section max-width="6xl">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><flux:heading size="xl">{{ $school->name }}</flux:heading><flux:text>{{ __('Academic structure') }}</flux:text></div>
        @if ($this->canManageAcademic())
            <flux:badge color="teal" icon="academic-cap">{{ __('Teacher workspace') }}</flux:badge>
        @else
            <flux:badge color="zinc" icon="eye">{{ __('Supervision view') }}</flux:badge>
        @endif
    </div>

    <flux:input wire:model.live="subjectSearch" :label="__('Search subjects')" placeholder="{{ __('Search by name or code...') }}" icon="magnifying-glass" />

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-teal-100 bg-teal-50/60 dark:border-zinc-800 dark:bg-zinc-800"><tr><th class="px-5 py-3">{{ __('Subject') }}</th><th class="px-5 py-3">{{ __('Classrooms') }}</th><th class="px-5 py-3"></th></tr></thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($this->subjects as $subject)
                    <tr wire:key="subject-{{ $subject->id }}">
                        <td class="px-5 py-4"><div class="font-medium">{{ $subject->name }}</div><div class="text-zinc-500">{{ $subject->code }}</div></td>
                        <td class="px-5 py-4"><div class="flex flex-wrap gap-2">@foreach($subject->learningEnvironments as $environment)<div wire:key="environment-{{ $environment->id }}" class="flex items-center gap-2 rounded-md border border-zinc-200 px-2 py-1 dark:border-zinc-700"><span>{{ $environment->name }}{{ $environment->section ? ' · '.$environment->section : '' }}</span>@can('manageMembers', $environment)<flux:button size="sm" :href="route('learning-environments.members', $environment)" wire:navigate icon="users">{{ __('Students') }}</flux:button>@endcan @can('delete', $environment)<flux:button size="sm" variant="danger" wire:click="deleteLearningEnvironment({{ $environment->id }})" wire:confirm="{{ __('Are you sure you want to delete this classroom?') }}" icon="trash">{{ __('Delete') }}</flux:button>@endcan</div>@endforeach</div></td>
                        <td class="px-5 py-4 text-right">@can('delete', $subject)<flux:button size="sm" variant="danger" wire:click="deleteSubject({{ $subject->id }})" wire:confirm="{{ __('Are you sure you want to delete this subject and all its classrooms?') }}" icon="trash">{{ __('Delete') }}</flux:button>@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-zinc-500">{{ __('No subjects have been created yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>

    {{ $this->subjects->links() }}
</x-page-section>