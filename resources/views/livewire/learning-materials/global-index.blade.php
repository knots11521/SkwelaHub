<x-page-section>
    <div>
        <flux:heading size="xl">{{ $isTeacher ? __('Learning Materials Overview') : __('Learning Materials Hub') }}
        </flux:heading>
        <flux:text>
            {{ $isTeacher ? __('Manage and view learning materials published across all your classes.') : __('Access learning materials published across all your enrolled classes.') }}
        </flux:text>
    </div>

    @forelse ($environments as $environment)
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                <div>
                    <flux:heading size="lg">{{ $environment->name }}</flux:heading>
                    @if ($environment->section)
                        <flux:text class="text-xs">{{ $environment->section }}</flux:text>
                    @endif
                </div>
                <flux:button :href="route('learning-environments.materials', $environment)" wire:navigate size="sm"
                    variant="subtle">
                    {{ $isTeacher ? __('Manage Materials') : __('View Class') }}
                </flux:button>
            </div>

            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($environment->materials as $material)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex flex-col">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $material->title }}</span>
                            @if ($material->type)
                                <span class="text-xs font-mono uppercase text-zinc-500">{{ $material->type }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($material->url)
                                <flux:button href="{{ $material->url }}" target="_blank" size="sm" variant="ghost"
                                    icon="arrow-top-right-on-square">
                                    {{ __('Open Link') }}
                                </flux:button>
                            @elseif ($material->file_path)
                                <flux:button href="{{ Storage::url($material->file_path) }}" target="_blank"
                                    size="sm" variant="ghost" icon="document-arrow-down">
                                    {{ __('Download') }}
                                </flux:button>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-2 text-sm text-zinc-500">
                        {{ $isTeacher ? __('You have not added any materials to this class yet.') : __('No materials posted in this class yet.') }}
                    </li>
                @endforelse
            </ul>
        </flux:card>
    @empty
        <flux:card>
            <p class="text-center text-zinc-500">
                {{ $isTeacher ? __('You are not assigned to any learning environments.') : __('You are not enrolled in any learning environments.') }}
            </p>
        </flux:card>
    @endforelse
</x-page-section>
