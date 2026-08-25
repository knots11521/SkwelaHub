<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $school->name }}</flux:heading>
            <flux:text>{{ __('Academic structure') }}</flux:text>
        </div>
        <flux:button :href="route('schools.members', $school)" wire:navigate>{{ __('School members') }}</flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card>
            <form wire:submit="createSubject" class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Add subject') }}</flux:heading>
                <flux:input wire:model="subjectName" :label="__('Name')" required />
                <flux:input wire:model="subjectCode" :label="__('Code')" required />
                <flux:button type="submit" variant="primary">{{ __('Create subject') }}</flux:button>
            </form>
        </flux:card>

        <flux:card>
            <form wire:submit="createLearningEnvironment" class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Add learning environment') }}</flux:heading>
                <flux:select wire:model="subjectId" :label="__('Subject')">
                    <flux:select.option value="">{{ __('Select subject') }}</flux:select.option>
                    @foreach($subjects as $subject)
                        <flux:select.option :value="$subject->id">{{ $subject->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="environmentName" :label="__('Class name')" required />
                <flux:input wire:model="section" :label="__('Section (optional)')" />
                <flux:button type="submit" variant="primary">{{ __('Create class') }}</flux:button>
            </form>
        </flux:card>
    </div>

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-5 py-3">{{ __('Subject') }}</th>
                    <th class="px-5 py-3">{{ __('Classes') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($subjects as $subject)
                    <tr wire:key="subject-{{ $subject->id }}">
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ $subject->name }}</div>
                            <div class="text-zinc-500">{{ $subject->code }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($subject->learningEnvironments as $environment)
                                    <div wire:key="environment-{{ $environment->id }}" class="flex items-center gap-2 rounded-md border border-zinc-200 px-2 py-1 dark:border-zinc-700">
                                        <span>{{ $environment->name }}{{ $environment->section ? ' · '.$environment->section : '' }}</span>
                                        <flux:button size="sm" :href="route('learning-environments.members', $environment)" wire:navigate>{{ __('Members') }}</flux:button>
                                        <button type="button" wire:click="deleteLearningEnvironment({{ $environment->id }})" class="text-zinc-500 hover:text-red-600" aria-label="{{ __('Delete :name', ['name' => $environment->name]) }}">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right"><flux:button size="sm" variant="danger" wire:click="deleteSubject({{ $subject->id }})">{{ __('Delete') }}</flux:button></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-zinc-500">{{ __('No subjects yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
</section>
