<x-page-section max-width="7xl">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('School management') }}</flux:heading>
            <flux:text>{{ __('Activate, suspend, and review schools across the platform.') }}</flux:text>
        </div>
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg">{{ __('All schools') }}</flux:heading>
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" placeholder="{{ __('Search by name or code...') }}" icon="magnifying-glass" class="max-w-xs" />
        </div>
    </flux:card>

    <flux:card class="overflow-x-auto p-0">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-5 py-3 font-medium">{{ __('Name') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Region') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Admin') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Members') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Subjects') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Environments') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Reviewed') }}</th>
                    <th class="px-5 py-3 font-medium">{{ __('Created') }}</th>
                    <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($schools as $school)
                    <tr wire:key="super-school-{{ $school->id }}">
                        <td class="px-5 py-4">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $school->name }}</div>
                            <div class="font-mono text-xs text-zinc-500">{{ $school->slug }}</div>
                        </td>
                        <td class="px-5 py-4 text-zinc-500">{{ $school->region ?? __('—') }}</td>
                        <td class="px-5 py-4">
                            @if ($school->creator)
                                <div class="font-medium">{{ $school->creator->name }}</div>
                                <div class="text-zinc-500">{{ $school->creator->email }}</div>
                            @else
                                <span class="text-zinc-500">{{ __('No admin assigned') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-zinc-500">{{ $school->memberships_count }}</td>
                        <td class="px-5 py-4 text-right text-zinc-500">{{ $school->subjects_count }}</td>
                        <td class="px-5 py-4 text-right text-zinc-500">{{ $school->learning_environments_count }}</td>
                        <td class="px-5 py-4">
                            <flux:badge color="{{ $school->status === 'active' ? 'emerald' : 'amber' }}">
                                {{ ucfirst($school->status ?? 'unknown') }}
                            </flux:badge>
                        </td>
                        <td class="px-5 py-4">
                            <flux:badge color="{{ $school->reviewed ? 'emerald' : 'amber' }}" variant="{{ $school->reviewed ? 'solid' : 'outline' }}">
                                {{ $school->reviewed ? __('Reviewed') : __('Unreviewed') }}
                            </flux:badge>
                        </td>
                        <td class="px-5 py-4 text-zinc-500">{{ $school->created_at?->format('M j, Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($school->status === 'active')
                                    <flux:button size="sm" variant="danger" wire:click="suspend({{ $school->id }})" wire:confirm="{{ __('Suspend :name? Members will not be able to sign in until reactivated.', ['name' => $school->name]) }}">
                                        {{ __('Suspend') }}
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="primary" wire:click="reactivate({{ $school->id }})" wire:confirm="{{ __('Reactivate :name?', ['name' => $school->name]) }}">
                                        {{ __('Reactivate') }}
                                    </flux:button>
                                @endif

                                @if ($school->reviewed)
                                    <flux:button size="sm" variant="ghost" wire:click="unmarkAsReviewed({{ $school->id }})">
                                        {{ __('Unmark reviewed') }}
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="subtle" wire:click="markAsReviewed({{ $school->id }})">
                                        {{ __('Mark reviewed') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-5 py-8 text-center text-zinc-500">{{ __('No schools found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>

    {{ $schools->links() }}
</x-page-section>