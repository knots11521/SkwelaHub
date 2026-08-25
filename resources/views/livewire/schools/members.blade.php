<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div class="space-y-1">
                <flux:heading size="xl">{{ $school->name }}</flux:heading>
                <flux:text>{{ __('Create and manage users who belong only to this school.') }}</flux:text>
            </div>
            <flux:button :href="route('dashboard')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Dashboard') }}</flux:button>
            <flux:button :href="route('schools.academic', $school)" wire:navigate variant="primary">{{ __('Academic structure') }}</flux:button>
        </div>
        <flux:card>
            <form wire:submit="createMember" class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2 flex items-center gap-2">
                    <flux:icon.user-plus class="size-5 text-teal-600 dark:text-teal-400" />
                    <flux:heading size="lg">{{ __('Add school user') }}</flux:heading>
                </div>
                <flux:input wire:model="memberName" :label="__('Name')" required />
                <flux:input wire:model="memberEmail" :label="__('Email address')" type="email" required />
                <flux:input wire:model="memberPassword" :label="__('Temporary password')" type="password" required viewable />
                <flux:select wire:model="memberRole" :label="__('Role')">
                    <flux:select.option value="{{ \App\SchoolRole::Teacher->value }}">{{ __('Teacher') }}</flux:select.option>
                    <flux:select.option value="{{ \App\SchoolRole::Student->value }}">{{ __('Student') }}</flux:select.option>
                    <flux:select.option value="{{ \App\SchoolRole::ParentGuardian->value }}">{{ __('Parent / Guardian') }}</flux:select.option>
                </flux:select>
                <div class="md:col-span-2 flex justify-end"><flux:button type="submit" variant="primary" icon="user-plus">{{ __('Create user') }}</flux:button></div>
            </form>
        </flux:card>

        <flux:card class="overflow-x-auto p-0">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('User') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Role') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Added') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->members as $member)
                        <tr wire:key="school-user-{{ $member->id }}">
                            <td class="px-5 py-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</div>
                                <div class="text-zinc-500">{{ $member->email }}</div>
                            </td>
                            <td class="px-5 py-4"><flux:badge color="teal">{{ $member->role?->value }}</flux:badge></td>
                            <td class="px-5 py-4 text-zinc-500">{{ $member->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-zinc-500">{{ __('No school users have been added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </flux:card>
        {{ $this->members->links() }}
</section>
