<x-layouts::auth :title="__('Register your school')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create a school account')" :description="__('Set up your school and administrator account in one step')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <flux:callout variant="warning" icon="information-circle">
            {{ __('Your school will be activated immediately and marked as unreviewed by default.') }}
        </flux:callout>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="role" value="{{ \App\SchoolRole::SchoolAdmin->value }}">

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
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

            <flux:separator />

            <!-- School Name -->
            <flux:input
                name="school_name"
                :label="__('School name')"
                :value="old('school_name')"
                type="text"
                required
                :placeholder="__('e.g. Mabinay National High School')"
            />

            <!-- School Address -->
            <flux:input
                name="school_address"
                :label="__('Address')"
                :value="old('school_address')"
                type="text"
                :placeholder="__('Street, barangay, city')"
            />

            <!-- School Region -->
            <flux:input
                name="school_region"
                :label="__('Region')"
                :value="old('school_region')"
                type="text"
                :placeholder="__('e.g. Region VII - Central Visayas')"
            />

            <!-- School Code (slug, optional) -->
            <flux:input
                name="school_slug"
                :label="__('School code (optional)')"
                :value="old('school_slug')"
                type="text"
                :placeholder="__('Unique short code used in URLs')"
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-school-admin-button">
                    {{ __('Create school and admin account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Not a school administrator?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Register as student, teacher, or parent') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>