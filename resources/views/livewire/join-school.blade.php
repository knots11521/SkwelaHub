<x-page-section max-width="md">
    <div class="text-center space-y-2">
        <flux:heading size="xl">{{ __('Join a classroom') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('Enter the classroom invite code shared by your teacher or school administrator.') }}
        </flux:text>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle">
            {{ session('error') }}
        </flux:callout>
    @endif

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="joinByInvite" class="flex flex-col gap-4">
        <flux:input
            wire:model="inviteCode"
            :label="__('Invite code')"
            type="text"
            required
            autofocus
            placeholder="{{ __('e.g. ABC12345') }}"
            class="font-mono uppercase tracking-wider"
        />

        @error('inviteCode')
            <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
        @enderror

        <flux:button type="submit" variant="primary" class="w-full" icon="link" data-test="join-school-button">
            {{ __('Join classroom') }}
        </flux:button>
    </form>

    <flux:text class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Don\'t have an invite code? Contact your teacher or school administrator to be added to a classroom.') }}
    </flux:text>

    <div class="text-center">
        <flux:link :href="route('dashboard')" wire:navigate>{{ __('Back to dashboard') }}</flux:link>
    </div>
</x-page-section>