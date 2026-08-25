<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="space-y-1">
                <flux:heading size="xl">{{ __('Schools') }}</flux:heading>
                <flux:text>{{ __('Find a school and request the access role that fits you.') }}</flux:text>
            </div>
            @if ($this->canCreateSchools)
                <flux:button variant="primary" wire:click="$set('showSchoolForm', true)">{{ __('Create school') }}</flux:button>
            @endif
        </div>
        <flux:modal wire:model="showSchoolForm" class="md:w-96">
            <form wire:submit="createSchool" class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ __('Create a school') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Schools are created and managed at the platform level.') }}</flux:text>
                </div>
                <flux:input wire:model="schoolName" :label="__('School name')" required autofocus />
                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" type="button" wire:click="$set('showSchoolForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">{{ __('Create school') }}</flux:button>
                </div>
            </form>
        </flux:modal>
        <flux:card class="flex flex-col gap-4">
            <div class="max-w-sm">
                <flux:select wire:model="requestedRole" :label="__('Role for a new request')">
                    @foreach (\App\SchoolRole::cases() as $role)
                        <flux:select.option :value="$role->value">{{ $role->value }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:text>{{ __('A request does not grant access until an authorized administrator approves it.') }}</flux:text>
        </flux:card>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->schools as $school)
                @php($membership = $school->memberships->first())
                <flux:card wire:key="school-{{ $school->id }}" class="flex flex-col gap-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <flux:heading size="lg">{{ $school->name }}</flux:heading>
                            @if ($school->description)
                                <flux:text>{{ $school->description }}</flux:text>
                            @endif
                        </div>
                        @if ($membership)
                            <flux:badge>{{ str($membership->status->value)->headline() }}</flux:badge>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        @if ($membership?->status === \App\SchoolMembershipStatus::Approved && auth()->user()->can('manageMemberships', $school))
                            <flux:button :href="route('schools.members', $school)" wire:navigate>{{ __('Manage members') }}</flux:button>
                        @elseif (! $membership || in_array($membership->status, [\App\SchoolMembershipStatus::Rejected, \App\SchoolMembershipStatus::Removed], true))
                            <flux:button variant="primary" wire:click="requestMembership({{ $school->id }})" wire:loading.attr="disabled">{{ __('Request access') }}</flux:button>
                        @elseif ($membership->status === \App\SchoolMembershipStatus::Pending)
                            <flux:text>{{ __('Awaiting review') }}</flux:text>
                        @else
                            <flux:text>{{ __('Access is unavailable') }}</flux:text>
                        @endif
                    </div>
                </flux:card>
            @empty
                <flux:card class="md:col-span-2 xl:col-span-3">
                    <flux:heading>{{ __('No schools are available yet.') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('A platform administrator can create the first school.') }}</flux:text>
                </flux:card>
            @endforelse
        </div>
        {{ $this->schools->links() }}
</section>
