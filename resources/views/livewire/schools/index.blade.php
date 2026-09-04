<x-page-section max-width="6xl">
    <div class="space-y-1">
        <div class="flex items-center gap-2"><flux:icon.building-office class="size-6 text-teal-600 dark:text-teal-400" /><flux:heading size="xl">{{ __('School directory') }}</flux:heading></div>
        <flux:text>{{ __('Each school is created and managed by its first School Admin during registration.') }}</flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->schools as $school)
            <flux:card wire:key="school-{{ $school->id }}" class="flex flex-col gap-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1"><flux:heading size="lg">{{ $school->name }}</flux:heading><flux:text>{{ $school->region ?: __('Region not provided') }}</flux:text></div>
                    <flux:badge color="teal">{{ __('Active') }}</flux:badge>
                </div>
                @if ($school->description)
                    <flux:text>{{ $school->description }}</flux:text>
                @endif
            </flux:card>
        @empty
            <flux:card class="md:col-span-2 xl:col-span-3"><flux:text>{{ __('No schools have been registered yet.') }}</flux:text></flux:card>
        @endforelse
    </div>

    {{ $this->schools->links() }}
</x-page-section>
