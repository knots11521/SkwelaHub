<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }} dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-teal-50/40 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">

    @php
        $user = auth()->user();
        $isGuestMode = $user && ! $user->hasApprovedSchoolMembership();

        $workspace = match (true) {
            $user->hasRole(\Database\Seeders\RoleSeeder::SuperAdmin) => __('Platform'),
            $user->hasRole(\App\SchoolRole::SchoolAdmin->value) => __('School administration'),
            $user->hasRole(\App\SchoolRole::Teacher->value) => __('Teaching'),
            $user->hasRole(\App\SchoolRole::Student->value) => __('Learning'),
            $user->hasRole(\App\SchoolRole::ParentGuardian->value) => __('Guardian access'),
            default => __('Workspace'),
        };
    @endphp

    {{-- Sidebar --}}
    <flux:sidebar sticky collapsible
        class="border-e border-zinc-200/80 bg-white/95 backdrop-blur-md transition-all duration-300 ease-in-out dark:border-zinc-800/80 dark:bg-zinc-900/95">

        {{-- Sidebar Header --}}
        <flux:sidebar.header class="gap-2">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        {{-- Main Navigation --}}
        <flux:sidebar.nav class="space-y-1.5">

            {{-- Workspace Badge Indicator --}}
            <div class="px-2 py-1 in-data-flux-sidebar-collapsed-desktop:hidden">
                <span
                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    {{ $workspace }}
                </span>
            </div>

            {{-- Dashboard --}}
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>

            @if ($user->canAccessJoinSchool())
                <flux:sidebar.item icon="building-office" :href="route('join-school')" :current="request()->routeIs('join-school')"
                    wire:navigate>
                    {{ __('Join School') }}
                </flux:sidebar.item>
            @endif

            @if (! $isGuestMode && $user->school_id && $user->hasRole(\App\SchoolRole::SchoolAdmin->value))
                <flux:sidebar.item icon="users" :href="route('schools.members', $user->school_id)" :current="request()->routeIs('schools.members')" wire:navigate>{{ __('School users') }}</flux:sidebar.item>
            @endif

            @if (! $isGuestMode && $user->school_id && $user->hasRole(\App\SchoolRole::SchoolAdmin->value))
                <flux:sidebar.item icon="academic-cap" :href="route('schools.academic', $user->school_id)" :current="request()->routeIs('schools.academic')" wire:navigate>{{ __('Academic supervision') }}</flux:sidebar.item>
            @endif

            {{-- Learning Hub (Dropdown for Teachers & Students) --}}
            @if (! $isGuestMode && ($user->hasRole(\App\SchoolRole::Teacher->value) || $user->hasRole(\App\SchoolRole::Student->value) || $user->hasRole(\App\SchoolRole::ParentGuardian->value)))
                <flux:sidebar.group expandable icon="academic-cap" :heading="__('Learning Hub')"
                    :open="request()->routeIs(['learning-environments.*', 'assessments.*', 'assignments.*', 'materials.*', 'performance.*'])">

                    {{-- Learning Environments --}}
                    <flux:sidebar.item icon="rectangle-stack" :href="route('learning-environments.index')"
                        :current="request()->routeIs('learning-environments.*')" wire:navigate>
                        {{ __('Learning Environments') }}
                    </flux:sidebar.item>

                    {{-- Global Assessments --}}
                    <flux:sidebar.item icon="document-text" :href="route('assessments.index')"
                        :current="request()->routeIs('assessments.*')" wire:navigate>
                        {{ __('Assessments') }}
                    </flux:sidebar.item>

                    {{-- Global Assignments --}}
                    <flux:sidebar.item icon="pencil-square" :href="route('assignments.index')"
                        :current="request()->routeIs('assignments.*')" wire:navigate>
                        {{ __('Assignments') }}
                    </flux:sidebar.item>

                    {{-- Global Materials --}}
                    <flux:sidebar.item icon="folder" :href="route('materials.index')"
                        :current="request()->routeIs('materials.*')" wire:navigate>
                        {{ __('Materials') }}
                    </flux:sidebar.item>

                    {{-- Student Performance Link --}}
                    @if ($user->hasRole(\App\SchoolRole::Student->value) || $user->hasRole(\App\SchoolRole::ParentGuardian->value))
                        <flux:sidebar.item icon="chart-bar" :href="route('performance.index')"
                            :current="request()->routeIs('performance.*')" wire:navigate>
                            {{ __('My performance') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            @endif

        </flux:sidebar.nav>

        {{-- Spacer --}}
        <flux:sidebar.spacer />

        {{-- Desktop User Menu Footer --}}
        <div
            class="p-2 border-t border-zinc-100 dark:border-zinc-800/80 overflow-x-hidden flex justify-start in-data-flux-sidebar-collapsed-desktop:justify-center">
            <x-desktop-user-menu class="hidden lg:block w-full max-w-full truncate" :name="auth()->user()->name" />
        </div>

    </flux:sidebar>

    {{-- Mobile Header --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">
                                    {{ auth()->user()->name }}
                                </flux:heading>
                                <flux:text class="truncate">
                                    {{ auth()->user()->email }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{-- Page Content --}}
    {{ $slot }}

    {{-- Toast --}}
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
