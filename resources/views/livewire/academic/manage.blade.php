<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><flux:heading size="xl">{{ $school->name }}</flux:heading><flux:text>{{ __('Academic structure') }}</flux:text></div>
        @if ($this->canManageAcademic())
            <flux:badge color="teal" icon="academic-cap">{{ __('Teacher workspace') }}</flux:badge>
        @else
            <flux:badge color="zinc" icon="eye">{{ __('Supervision view') }}</flux:badge>
        @endif
    </div>

    @if ($this->canManageAcademic())
        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <form wire:submit="createSubject" class="flex flex-col gap-4">
                    <div class="flex items-center gap-2"><flux:icon.book-open class="size-5 text-teal-600 dark:text-teal-400" /><flux:heading size="lg">{{ __('Create subject') }}</flux:heading></div>
                    <flux:input wire:model="subjectName" :label="__('Name')" required />
                    <flux:input wire:model="subjectCode" :label="__('Code')" required />
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Create subject') }}</flux:button>
                </form>
            </flux:card>

            <flux:card>
                <form wire:submit="createLearningEnvironment" class="flex flex-col gap-4">
                    <div class="flex items-center gap-2"><flux:icon.rectangle-stack class="size-5 text-teal-600 dark:text-teal-400" /><flux:heading size="lg">{{ __('Create classroom') }}</flux:heading></div>
                    <flux:select wire:model="subjectId" :label="__('Your subject')"><flux:select.option value="">{{ __('Select subject') }}</flux:select.option>@foreach($subjects as $subject)<flux:select.option :value="$subject->id">{{ $subject->name }}</flux:select.option>@endforeach</flux:select>
                    <flux:input wire:model="environmentName" :label="__('Class name')" required />
                    <flux:input wire:model="section" :label="__('Section (optional)')" />
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Create classroom') }}</flux:button>
                </form>
            </flux:card>
        </div>
    @endif

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-teal-100 bg-teal-50/60 dark:border-zinc-800 dark:bg-zinc-800"><tr><th class="px-5 py-3">{{ __('Subject') }}</th><th class="px-5 py-3">{{ __('Classrooms') }}</th><th class="px-5 py-3"></th></tr></thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($subjects as $subject)
                    <tr wire:key="subject-{{ $subject->id }}">
                        <td class="px-5 py-4"><div class="font-medium">{{ $subject->name }}</div><div class="text-zinc-500">{{ $subject->code }}</div></td>
                        <td class="px-5 py-4"><div class="flex flex-wrap gap-2">@foreach($subject->learningEnvironments as $environment)<div wire:key="environment-{{ $environment->id }}" class="flex items-center gap-2 rounded-md border border-zinc-200 px-2 py-1 dark:border-zinc-700"><span>{{ $environment->name }}{{ $environment->section ? ' · '.$environment->section : '' }}</span>@can('manageMembers', $environment)<flux:button size="sm" :href="route('learning-environments.members', $environment)" wire:navigate icon="users">{{ __('Students') }}</flux:button>@endcan @can('delete', $environment)<flux:button size="sm" variant="danger" wire:click="deleteLearningEnvironment({{ $environment->id }})" icon="trash">{{ __('Delete') }}</flux:button>@endcan</div>@endforeach</div></td>
                        <td class="px-5 py-4 text-right">@can('delete', $subject)<flux:button size="sm" variant="danger" wire:click="deleteSubject({{ $subject->id }})" icon="trash">{{ __('Delete') }}</flux:button>@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-zinc-500">{{ $this->canManageAcademic() ? __('Create your first subject to begin.') : __('No teacher-owned subjects have been created yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
</section>
