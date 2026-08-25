<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>

    @include('partials.head')

</head>

<body class="min-h-screen bg-teal-50/40 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">

    @php

        $user = auth()->user();

        $workspace = match (true) {
            $user->hasRole(\Database\Seeders\RoleSeeder::SuperAdmin) => __('Platform'),

            $user->hasRole(\App\SchoolRole::SchoolAdmin->value) => __('School administration'),

            $user->hasRole(\App\SchoolRole::Teacher->value) => __('Teaching'),

            $user->hasRole(\App\SchoolRole::Student->value) => __('Learning'),

            $user->hasRole(\App\SchoolRole::ParentGuardian->value) => __('Guardian access'),

            default => __('Workspace'),
        };

    @endphp


    {{-- ========================================================= --}}
    {{-- Sidebar --}}
    {{-- ========================================================= --}}

    <flux:sidebar sticky collapsible
        class="border-e border-teal-100 bg-white/95 transition-[width] duration-300 ease-in-out dark:border-zinc-800 dark:bg-zinc-900">

        {{-- Sidebar Header --}}
        <flux:sidebar.header>

            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

            {{-- Desktop Collapse Button --}}
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />

        </flux:sidebar.header>


        {{-- ===================================================== --}}
        {{-- Main Navigation --}}
        {{-- ===================================================== --}}

        <flux:sidebar.nav>

            {{-- Workspace --}}
            <flux:heading
                class="px-2 text-xs font-medium text-zinc-500 transition-opacity duration-200 ease-in-out dark:text-zinc-400 in-data-flux-sidebar-collapsed-desktop:hidden">
                {{ $workspace }}
            </flux:heading>


            {{-- Dashboard --}}
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>


            {{-- Schools --}}
            <flux:sidebar.item icon="building-office-2" :href="route('schools.index')"
                :current="request()->routeIs('schools.*')" wire:navigate>

                @if ($user->hasRole(\Database\Seeders\RoleSeeder::SuperAdmin))
                    {{ __('Manage schools') }}
                @else
                    {{ __('School access') }}
                @endif

            </flux:sidebar.item>


            {{-- Membership Requests --}}
            @if (
                $user->hasRole(\Database\Seeders\RoleSeeder::SuperAdmin) ||
                    $user->hasRole(\App\SchoolRole::SchoolAdmin->value) ||
                    $user->hasRole(\App\SchoolRole::Teacher->value))
                <flux:sidebar.item icon="clipboard-document-check" :href="route('membership-requests.index')"
                    :current="request()->routeIs('membership-requests.*')" wire:navigate>
                    {{ __('Membership requests') }}
                </flux:sidebar.item>
            @endif


            {{-- Student Performance --}}
            @if ($user->hasRole(\App\SchoolRole::Student->value))
                <flux:sidebar.item icon="chart-bar" :href="route('performance.index')"
                    :current="request()->routeIs('performance.*')" wire:navigate>
                    {{ __('My performance') }}
                </flux:sidebar.item>
            @endif

        </flux:sidebar.nav>


        {{-- ===================================================== --}}
        {{-- Spacer --}}
        {{-- ===================================================== --}}

        <flux:sidebar.spacer />


        {{-- ===================================================== --}}
        {{-- Desktop User Menu --}}
        {{-- ===================================================== --}}

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />

    </flux:sidebar>


    {{-- ========================================================= --}}
    {{-- Mobile Header --}}
    {{-- ========================================================= --}}

    <flux:header class="lg:hidden">

        {{-- Mobile Sidebar Toggle --}}
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />


        {{-- Mobile User Menu --}}
        <flux:dropdown position="top" align="end">

            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>

                {{-- User Information --}}
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


                {{-- Settings --}}
                <flux:menu.radio.group>

                    <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>

                </flux:menu.radio.group>


                <flux:menu.separator />


                {{-- Logout --}}
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


    {{-- ========================================================= --}}
    {{-- Page Content --}}
    {{-- ========================================================= --}}

    {{ $slot }}


    {{-- ========================================================= --}}
    {{-- Toast --}}
    {{-- ========================================================= --}}

    @persist('toast')
        <flux:toast.group>

            <flux:toast />

        </flux:toast.group>
    @endpersist


    {{-- ========================================================= --}}
    {{-- Flux Scripts --}}
    {{-- ========================================================= --}}

    @fluxScripts

</body>

</html>
