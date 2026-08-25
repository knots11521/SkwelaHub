<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="space-y-1">
        <flux:heading size="xl">{{ __('Membership requests') }}</flux:heading>
        <flux:text>{{ __('Review only the requests assigned to your role. Approval grants school access; classroom enrollment remains a separate step.') }}</flux:text>
    </div>

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-teal-100 bg-teal-50/60 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-5 py-3 font-medium">{{ __('Applicant') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('School') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Requested role') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Requested') }}</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($this->requests as $membership)
                    <tr wire:key="membership-request-{{ $membership->id }}">
                        <td class="px-5 py-4">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $membership->user->name }}</div>
                            <div class="text-zinc-500">{{ $membership->user->email }}</div>
                        </td>
                        <td class="px-5 py-4">{{ $membership->school->name }}</td>
                        <td class="px-5 py-4"><flux:badge color="teal">{{ $membership->requested_role->value }}</flux:badge></td>
                        <td class="px-5 py-4 text-zinc-500">{{ $membership->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-4">
                            @can('approve', $membership)
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" variant="primary" wire:click="approve({{ $membership->id }})" wire:loading.attr="disabled">{{ __('Approve') }}</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="reject({{ $membership->id }})" wire:loading.attr="disabled">{{ __('Reject') }}</flux:button>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-zinc-500">{{ __('There are no membership requests assigned to you.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>

    {{ $this->requests->links() }}
</section>
