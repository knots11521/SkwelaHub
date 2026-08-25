<section class="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $learningEnvironment->name }}</flux:heading>
            <flux:text>{{ __('Teacher assignments and student enrollment') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('schools.academic', $learningEnvironment->school)" wire:navigate>{{ __('Academic structure') }}</flux:button>
            <flux:button :href="route('learning-environments.materials', $learningEnvironment)" wire:navigate>{{ __('Materials') }}</flux:button>
        </div>
    </div>

    <flux:card>
        <form wire:submit="addMember" class="flex flex-col gap-3 sm:flex-row">
            <flux:select wire:model="schoolMembershipId" class="grow">
                <flux:select.option value="">{{ __('Select approved teacher or student') }}</flux:select.option>
                @foreach($available as $member)
                    <flux:select.option :value="$member->id">{{ $member->user->name }} · {{ $member->requested_role->value }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="primary" type="submit">{{ __('Add') }}</flux:button>
        </form>
    </flux:card>

    <flux:card>
        <div class="flex flex-col gap-3">
            @forelse($memberships as $membership)
                <div wire:key="member-{{ $membership->id }}" class="flex items-center justify-between gap-3">
                    <div>{{ $membership->schoolMembership->user->name }} <span class="text-zinc-500">{{ $membership->schoolMembership->requested_role->value }}</span></div>
                    <flux:button size="sm" variant="danger" wire:click="removeMember({{ $membership->id }})">{{ __('Remove') }}</flux:button>
                </div>
            @empty
                <flux:text>{{ __('No teacher or student has been assigned yet.') }}</flux:text>
            @endforelse
        </div>
    </flux:card>
</section>
