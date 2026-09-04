<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-6 py-12 dark:from-neutral-950 dark:to-neutral-900">
        <div class="max-w-md w-full text-center">
            <div class="mb-8 flex justify-center">
                <div
                    class="relative p-4 bg-teal-50 rounded-full border border-teal-100 shadow-sm transition-transform hover:scale-110 duration-300 dark:bg-teal-900/20 dark:border-teal-800">
                    @yield('icon', '<svg class="w-12 h-12 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>')
                    <span class="absolute -top-1 -right-1 flex h-6 w-6">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-20"></span>
                        <span
                            class="relative inline-flex rounded-full h-6 w-6 bg-teal-500 items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </span>
                </div>
            </div>

            <h1
                class="text-9xl font-extrabold tracking-tighter text-teal-600 opacity-20 select-none">
                @yield('code')
            </h1>

            <div class="-mt-12">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl dark:text-slate-100">
                    @yield('title')
                </h2>
                <p class="mt-4 text-base leading-7 text-slate-600 dark:text-zinc-400">
                    @yield('message')
                </p>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <flux:button variant="primary" href="{{ route('home') }}" wire:navigate>
                    {{ __('Go Home') }}
                </flux:button>
                <flux:button variant="ghost" href="javascript:history.back()">
                    {{ __('Go Back') }}
                </flux:button>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
