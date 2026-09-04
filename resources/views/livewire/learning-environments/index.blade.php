<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">
                {{ $isTeacher ? __('My Teaching Environments') : __('My Learning Environments') }}
            </flux:heading>
            <flux:text>
                {{ $isTeacher ? __('Access and manage your active teaching classrooms.') : __('Access and view your enrolled subjects and classrooms.') }}
            </flux:text>
        </div>

        @if ($isTeacher)
            <div class="flex gap-2">
                <flux:button wire:click="openCreateEnvironmentModal" variant="primary" icon="plus">
                    {{ __('Create Classroom') }}
                </flux:button>
            </div>
        @endif
    </div>

    <flux:input wire:model.live="environmentSearch" :label="__('Search environments')" placeholder="{{ __('Search by name, subject, or school...') }}" icon="magnifying-glass" />

    <div class="grid gap-5 md:grid-cols-2">
        @forelse($this->environments as $environment)
            @include('livewire.partials.environment-card', ['environment' => $environment, 'isTeacher' => $isTeacher])
        @empty
            <flux:card
                class="md:col-span-2 border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center py-12 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mb-3">
                    <flux:icon icon="academic-cap" class="size-6" />
                </div>
                <flux:heading size="md" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ __('No environments found') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-500 max-w-sm mt-1">
                    {{ $isTeacher ? __('Create a subject and classroom to get started.') : __('You have not been assigned or enrolled in a learning environment yet. Reach out to your administrator to get started.') }}
                </flux:text>
            </flux:card>
        @endforelse
    </div>

    {{ $this->environments->links() }}

    @if ($isTeacher)
        <flux:modal wire:model="showCreateEnvironmentModal">
            <form wire:submit="createLearningEnvironment" class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Create classroom') }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ __('Create a new subject and classroom together.') }}</flux:text>
                </div>

                <flux:input wire:model="subjectName" :label="__('Subject name')" required />
                <flux:input wire:model="environmentName" :label="__('Class name')" required />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="showCreateEnvironmentModal = false">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Create classroom') }}</flux:button>
                </div>
            </form>
        </flux:modal>

        <flux:modal wire:model="showEditEnvironmentModal">
            <form wire:submit="updateLearningEnvironment" class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Edit classroom') }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ __('Update classroom details.') }}</flux:text>
                </div>
                <flux:input wire:model="subjectName" :label="__('Subject name')" required />
                <flux:input wire:model="environmentName" :label="__('Class name')" required />
                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="showEditEnvironmentModal = false">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</x-page-section>