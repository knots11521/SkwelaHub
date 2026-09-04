<x-page-section max-width="6xl">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div class="space-y-1">
            <flux:heading size="xl">{{ $school->name }}</flux:heading>
            <flux:text>{{ __('School members') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('dashboard')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Dashboard') }}</flux:button>
            <flux:button :href="route('schools.academic', $school)" wire:navigate variant="primary">{{ __('Academic structure') }}</flux:button>
        </div>
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <flux:icon.link class="size-5 text-teal-600 dark:text-teal-400" />
                <flux:heading size="lg">{{ __('School invites') }}</flux:heading>
            </div>
            <flux:button variant="filled" icon="plus" wire:click="openInviteModal">{{ __('Generate teacher invite') }}</flux:button>
        </div>
    </flux:card>

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-5 py-3 font-medium">{{ __('Code') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Link') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Role') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Expires') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->invites as $invite)
                    <tr wire:key="school-invite-{{ $invite->id }}">
                        <td class="px-5 py-4 font-mono text-xs">{{ $invite->code }}</td>
                        <td class="px-5 py-4 text-xs text-teal-600 dark:text-teal-400 max-w-xs truncate">{{ $invite->link }}</td>
                        <td class="px-5 py-4"><flux:badge color="teal">{{ $invite->role->label() }}</flux:badge></td>
                        <td class="px-5 py-4 text-zinc-500">{{ $invite->expires_at?->format('M j, Y') ?? 'Never' }}</td>
                        <td class="px-5 py-4 text-right">
                            <flux:button variant="danger" size="sm" wire:click="revokeInvite({{ $invite->id }})" wire:confirm="Revoke this invite?">{{ __('Revoke') }}</flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-zinc-500">{{ __('No school invites generated yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-5 py-3 font-medium">{{ __('User') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Role') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Added') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->members as $member)
                    <tr wire:key="school-user-{{ $member->id }}">
                        <td class="px-5 py-4">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</div>
                            <div class="text-zinc-500">{{ $member->email }}</div>
                        </td>
                        <td class="px-5 py-4"><flux:badge color="teal">{{ $member->getRoleNames()->first() ?? '—' }}</flux:badge></td>
                        <td class="px-5 py-4 text-zinc-500">{{ $member->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-4 text-right">
                            <flux:button variant="danger" size="sm" wire:click="removeUserFromSchool({{ $member->id }})" wire:confirm="{{ __('Remove this user from the school? Their classroom enrollments will also be removed.') }}" icon="trash">{{ __('Remove') }}</flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-zinc-500">{{ __('No school users have been added yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
    {{ $this->members->links() }}

    <flux:modal wire:model="showInviteModal">
        <div class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">{{ __('Generate teacher invite') }}</flux:heading>
                <flux:text class="text-zinc-500">{{ __('Teachers will join the school using the code or link below.') }}</flux:text>
            </div>

            @if ($lastGeneratedInvite)
                <flux:card class="bg-emerald-50/50 dark:bg-emerald-900/20">
                    <div class="flex flex-col gap-3">
                        <flux:text class="font-semibold">{{ __('Invite ready') }}</flux:text>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Code') }}</flux:text>
                            <div class="mt-1 font-mono text-lg">{{ $lastGeneratedInvite->code }}</div>
                        </div>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Link') }}</flux:text>
                            <div class="mt-1 break-all text-sm text-teal-600 dark:text-teal-400">{{ $lastGeneratedInvite->link }}</div>
                        </div>
                        <div>
                            <flux:badge color="teal">{{ $lastGeneratedInvite->role->label() }}</flux:badge>
                            @if ($lastGeneratedInvite->expires_at)
                                <flux:text class="ml-2 text-xs text-zinc-500">{{ __('Expires') }} {{ $lastGeneratedInvite->expires_at->format('M j, Y') }}</flux:text>
                            @endif
                        </div>
                    </div>
                </flux:card>
                <div class="flex justify-end">
                    <flux:button variant="ghost" wire:click="showInviteModal = false">{{ __('Close') }}</flux:button>
                </div>
            @else
                <form wire:submit="generateInvite" class="flex flex-col gap-4">
                    <flux:input type="date" wire:model="inviteExpiresAt" :label="__('Expires on (optional)')" />

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="showInviteModal = false">{{ __('Cancel') }}</flux:button>
                        <flux:button type="submit" variant="primary" icon="link">{{ __('Generate') }}</flux:button>
                    </div>
                </form>
            @endif
        </div>
    </flux:modal>
</x-page-section>
