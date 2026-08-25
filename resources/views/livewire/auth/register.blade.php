<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create your school')" :description="__('Your account will be the first School Admin for this school.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <div class="space-y-4 rounded-lg border border-teal-100 bg-teal-50/60 p-4 dark:border-teal-900 dark:bg-teal-950/30">
                <div class="flex items-center gap-2">
                    <flux:icon.building-office class="size-5 text-teal-600 dark:text-teal-400" />
                    <flux:heading size="lg">{{ __('School details') }}</flux:heading>
                </div>

                <flux:input name="school_name" :label="__('School name')" :value="old('school_name')" required autofocus autocomplete="organization" />
                <flux:input name="school_address" :label="__('Address')" :value="old('school_address')" autocomplete="street-address" />
                <flux:input name="school_region" :label="__('Region')" :value="old('school_region')" autocomplete="address-level1" />
                <flux:input name="school_slug" :label="__('School code (optional)')" :value="old('school_slug')" placeholder="my-school" autocomplete="off" />
            </div>

            <div class="flex items-center gap-2">
                <flux:icon.user-circle class="size-5 text-teal-600 dark:text-teal-400" />
                <flux:heading size="lg">{{ __('School Admin details') }}</flux:heading>
            </div>

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create school and admin account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
