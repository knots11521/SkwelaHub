<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div class="space-y-1">
                <flux:heading size="xl">{{ $school->name }}</flux:heading>
                <flux:text>{{ __('Review membership requests and see school access status.') }}</flux:text>
            </div>
            <flux:button :href="route('schools.index')" wire:navigate variant="ghost">{{ __('All schools') }}</flux:button>
            <flux:button :href="route('schools.academic', $school)" wire:navigate variant="primary">{{ __('Academic structure') }}</flux:button>
        </div>
        <flux:card class="overflow-x-auto p-0">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('User') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Requested role') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Requested') }}</th>
                        <th class="px-5 py-3 font-medium"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->memberships as $membership)
                        <tr wire:key="membership-{{ $membership->id }}">
                            <td class="px-5 py-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $membership->user->name }}</div>
                                <div class="text-zinc-500">{{ $membership->user->email }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $membership->requested_role->value }}</td>
                            <td class="px-5 py-4"><flux:badge>{{ str($membership->status->value)->headline() }}</flux:badge></td>
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
                            <td colspan="5" class="px-5 py-8 text-center text-zinc-500">{{ __('No membership requests yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </flux:card>
        {{ $this->memberships->links() }}
</section>
