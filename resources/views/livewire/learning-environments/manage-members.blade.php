<x-page-section max-width="4xl">
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

    @if ($this->availableClassroomRoles !== [])
        <flux:card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.link class="size-5 text-teal-600 dark:text-teal-400" />
                    <flux:heading size="lg">{{ __('Classroom invites') }}</flux:heading>
                </div>
                <flux:button variant="filled" icon="plus" wire:click="openInviteModal">{{ __('Generate invite') }}</flux:button>
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
                    @forelse ($this->classroomInvites as $invite)
                        <tr wire:key="invite-{{ $invite->id }}">
                            <td class="px-5 py-4 font-mono text-xs">{{ $invite->code }}</td>
                            <td class="px-5 py-4 text-xs text-teal-600 dark:text-teal-400 max-w-xs truncate">{{ $invite->link }}</td>
                            <td class="px-5 py-4"><flux:badge color="teal">{{ $invite->role?->label() }}</flux:badge></td>
                            <td class="px-5 py-4 text-zinc-500">{{ $invite->expires_at?->format('M j, Y') ?? 'Never' }}</td>
                            <td class="px-5 py-4 text-right">
                                <flux:button variant="danger" size="sm" wire:click="revokeClassroomInvite({{ $invite->id }})" wire:confirm="Revoke this invite?">{{ __('Revoke') }}</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-zinc-500">{{ __('No classroom invites generated yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </flux:card>
    @endif

    <flux:card>
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="md">{{ __('Class members') }}</flux:heading>
                <flux:input wire:model.live="memberSearch" :label="__('Search members')" placeholder="{{ __('Search by name or email...') }}" icon="magnifying-glass" class="max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-5 py-3 font-medium">{{ __('Member') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('Role') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('Date joined') }}</th>
                            <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->memberships as $membership)
                            <tr wire:key="member-{{ $membership->id }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :initials="$membership->schoolMembership->user->initials()" />
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $membership->schoolMembership->user->name }}</div>
                                            <div class="text-sm text-zinc-500">{{ $membership->schoolMembership->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <flux:badge color="teal">{{ $membership->schoolMembership->requested_role->label() }}</flux:badge>
                                </td>
                                <td class="px-5 py-4 text-zinc-500">{{ $membership->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" wire:click="viewMember({{ $membership->id }})" icon="eye">{{ __('View') }}</flux:button>
                                        <flux:button size="sm" variant="danger" wire:click="removeMember({{ $membership->id }})" wire:confirm="{{ __('Are you sure you want to remove this member from the class?') }}" icon="trash">{{ __('Remove') }}</flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-zinc-500">{{ __('No members have been assigned yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $this->memberships->links() }}
        </div>
    </flux:card>

    <flux:modal wire:model="showMemberDetailModal">
        @if ($this->selectedMember)
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <flux:avatar :initials="$this->selectedMember->schoolMembership->user->initials()" size="lg" />
                    <div>
                        <flux:heading size="lg">{{ $this->selectedMember->schoolMembership->user->name }}</flux:heading>
                        <flux:text class="text-zinc-500">{{ $this->selectedMember->schoolMembership->user->email }}</flux:text>
                    </div>
                </div>

                <flux:card class="bg-zinc-50/50 dark:bg-zinc-900/50">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Role') }}</flux:text>
                            <div class="mt-1">
                                <flux:badge color="teal">{{ $this->selectedMember->schoolMembership->user->getRoleNames()->first() ?? '—' }}</flux:badge>
                            </div>
                        </div>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Class role') }}</flux:text>
                            <div class="mt-1">
                                <flux:badge color="indigo">{{ $this->selectedMember->schoolMembership->requested_role->label() }}</flux:badge>
                            </div>
                        </div>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Date joined') }}</flux:text>
                            <div class="mt-1">{{ $this->selectedMember->created_at->format('M j, Y') }}</div>
                        </div>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Account created') }}</flux:text>
                            <div class="mt-1">{{ $this->selectedMember->schoolMembership->user->created_at->format('M j, Y') }}</div>
                        </div>
                        <div>
                            <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Membership status') }}</flux:text>
                            <div class="mt-1">
                                <flux:badge color="{{ $this->selectedMember->schoolMembership->status === \App\SchoolMembershipStatus::Approved ? 'emerald' : 'amber' }}">
                                    {{ $this->selectedMember->schoolMembership->status->label() }}
                                </flux:badge>
                            </div>
                        </div>
                        @if ($this->selectedMember->schoolMembership->reviewed_at)
                            <div>
                                <flux:text class="text-xs uppercase tracking-wider font-semibold text-zinc-500">{{ __('Reviewed') }}</flux:text>
                                <div class="mt-1">{{ $this->selectedMember->schoolMembership->reviewed_at->format('M j, Y g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                </flux:card>

                <div class="flex justify-end">
                    <flux:button variant="ghost" wire:click="showMemberDetailModal = false">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal wire:model="showInviteModal">
        <div class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">{{ __('Generate classroom invite') }}</flux:heading>
                <flux:text class="text-zinc-500">{{ __('Share the code or link with the person you want to invite.') }}</flux:text>
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
                <form wire:submit="generateClassroomInvite" class="flex flex-col gap-4">
                    <flux:select wire:model="classroomInviteRole" :label="__('Invite role')">
                        @foreach ($this->availableClassroomRoles as $role)
                            <flux:select.option value="{{ $role->value }}">{{ $role->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($this->classroomInviteRole === \App\SchoolRole::ParentGuardian->value)
                        <flux:select wire:model="classroomInviteStudentId" :label="__('Student')">
                            <flux:select.option value="">{{ __('Select a student') }}</flux:select.option>
                            @foreach ($this->students as $student)
                                <flux:select.option value="{{ $student->id }}">{{ $student->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif

                    <flux:input type="date" wire:model="classroomInviteExpiresAt" :label="__('Expires on (optional)')" />

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="showInviteModal = false">{{ __('Cancel') }}</flux:button>
                        <flux:button type="submit" variant="primary" icon="link">{{ __('Generate') }}</flux:button>
                    </div>
                </form>
            @endif
        </div>
    </flux:modal>
</x-page-section>
