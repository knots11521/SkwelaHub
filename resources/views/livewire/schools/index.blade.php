<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Page Header Section --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span
                    class="text-xs font-semibold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase">{{ __('Directory') }}</span>
            </div>
            <flux:heading size="xl" class="font-extrabold tracking-tight">
                {{ __('Schools & Access') }}
            </flux:heading>
            <flux:text class="text-zinc-500">
                {{ __('Find a school and request the access role that fits your profile.') }}
            </flux:text>
        </div>

        @if ($this->canCreateSchools)
            <div>
                <flux:button variant="primary" icon="plus" wire:click="$set('showSchoolForm', true)" class="shadow-sm">
                    {{ __('Create school') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Create School Modal --}}
    <flux:modal wire:model="showSchoolForm" class="md:w-[28rem] rounded-2xl">
        <form wire:submit="createSchool" class="space-y-6 p-2">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div
                        class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon icon="building-office-2" class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">{{ __('Create a School') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ __('Platform-level provisioning') }}
                        </flux:text>
                    </div>
                </div>
                <flux:text class="text-xs text-zinc-500 leading-relaxed pt-1">
                    {{ __('Schools are created and managed at the platform level. Ensure the name is accurate.') }}
                </flux:text>
            </div>

            <flux:input wire:model="schoolName" :label="__('School name')" placeholder="e.g. Oakridge Academy" required
                autofocus />

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showSchoolForm', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    {{ __('Create school') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Configuration & Info Card --}}
    <flux:card class="p-5 border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 rounded-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="max-w-xs w-full">
                <flux:select wire:model="requestedRole" :label="__('Role for new requests')">
                    @foreach (\App\SchoolRole::cases() as $role)
                        <flux:select.option :value="$role->value">{{ $role->value }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div
                class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 px-3.5 py-2.5 rounded-lg border border-zinc-200/60 dark:border-zinc-700/60 shadow-sm sm:max-w-md">
                <flux:icon icon="information-circle" class="size-4 text-zinc-400 shrink-0" />
                <span>{{ __('Requests do not grant access until an authorized administrator approves your profile.') }}</span>
            </div>
        </div>
    </flux:card>

    {{-- Schools Grid --}}
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->schools as $school)
            @php
                $membership = $school->memberships->first();
                $statusValue = $membership?->status->value ?? null;
            @endphp
            <flux:card wire:key="school-{{ $school->id }}"
                class="flex flex-col justify-between space-y-5 border border-zinc-200/80 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-200 hover:shadow-md">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">
                                {{ $school->name }}
                            </flux:heading>
                            @if ($school->description)
                                <flux:text size="sm" class="line-clamp-2 text-zinc-500">
                                    {{ $school->description }}
                                </flux:text>
                            @endif
                        </div>

                        @if ($membership)
                            @if ($statusValue === 'approved')
                                <flux:badge size="sm" variant="pill" color="emerald">
                                    {{ str($statusValue)->headline() }}
                                </flux:badge>
                            @elseif ($statusValue === 'pending')
                                <flux:badge size="sm" variant="pill" color="amber">
                                    {{ str($statusValue)->headline() }}
                                </flux:badge>
                            @elseif ($statusValue === 'rejected' || $statusValue === 'removed')
                                <flux:badge size="sm" variant="pill" color="rose">
                                    {{ str($statusValue)->headline() }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" variant="pill" color="zinc">
                                    {{ str($statusValue)->headline() }}
                                </flux:badge>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    @if ($membership?->status === \App\SchoolMembershipStatus::Approved && auth()->user()->can('manageMemberships', $school))
                        <flux:button :href="route('schools.members', $school)" wire:navigate size="sm"
                            icon="users">
                            {{ __('Manage members') }}
                        </flux:button>
                    @elseif (
                        !$membership ||
                            in_array(
                                $membership->status,
                                [\App\SchoolMembershipStatus::Rejected, \App\SchoolMembershipStatus::Removed],
                                true))
                        <flux:button variant="primary" size="sm" icon="key"
                            wire:click="requestMembership({{ $school->id }})" wire:loading.attr="disabled">
                            {{ __('Request access') }}
                        </flux:button>
                    @elseif ($membership->status === \App\SchoolMembershipStatus::Pending)
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ __('Awaiting review') }}
                        </span>
                    @else
                        <span class="text-xs text-zinc-400 font-medium">
                            {{ __('Access unavailable') }}
                        </span>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card
                class="md:col-span-2 xl:col-span-3 border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center py-12 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mb-3">
                    <flux:icon icon="building-office-2" class="size-6" />
                </div>
                <flux:heading size="md" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ __('No schools available') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-500 max-w-sm mt-1">
                    {{ __('A platform administrator can create the first school to get started.') }}
                </flux:text>
            </flux:card>
        @endforelse
    </div>

    {{-- Pagination Links --}}
    @if ($this->schools->hasPages())
        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $this->schools->links() }}
        </div>
    @endif
</div>
