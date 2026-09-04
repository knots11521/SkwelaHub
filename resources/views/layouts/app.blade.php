<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="md:!p-10 lg:!p-10">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
