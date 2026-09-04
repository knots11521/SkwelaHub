<flux:card
    wire:key="environment-card-{{ $environment->id }}"
    class="flex flex-col gap-4 border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200"
>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">
                {{ $environment->name }}
            </flux:heading>
            <flux:text class="text-sm text-zinc-500">
                {{ $environment->subject?->name ?? __('No subject') }} · {{ $environment->school?->name ?? __('No school') }}
            </flux:text>
        </div>
        @if ($environment->section)
            <flux:badge color="indigo" variant="pill">{{ $environment->section }}</flux:badge>
        @endif
    </div>

    @if ($environment->description)
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
            {{ $environment->description }}
        </flux:text>
    @endif

    <div class="flex flex-wrap gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
        <flux:button :href="route('learning-environments.materials', $environment)" wire:navigate size="sm" variant="subtle" icon="folder">
            {{ __('Materials') }}
        </flux:button>
        <flux:button :href="route('learning-environments.assignments', $environment)" wire:navigate size="sm" variant="subtle" icon="pencil-square">
            {{ __('Assignments') }}
        </flux:button>
        <flux:button :href="route('learning-environments.assessments', $environment)" wire:navigate size="sm" variant="subtle" icon="document-text">
            {{ __('Assessments') }}
        </flux:button>
        <flux:button :href="route('learning-environments.ai-assistance', $environment)" wire:navigate size="sm" variant="subtle" icon="sparkles">
            {{ __('AI Assistant') }}
        </flux:button>

        @if ($isTeacher)
            <flux:button :href="route('learning-environments.members', $environment)" wire:navigate size="sm" variant="subtle" icon="users">
                {{ __('Students') }}
            </flux:button>
            <flux:button wire:click="editLearningEnvironment({{ $environment->id }})" size="sm" variant="ghost" icon="pencil">
                {{ __('Edit') }}
            </flux:button>
            <flux:button
                wire:click="deleteLearningEnvironment({{ $environment->id }})"
                wire:confirm="{{ __('Delete this classroom? This cannot be undone.') }}"
                size="sm"
                variant="danger"
                icon="trash"
            >
                {{ __('Delete') }}
            </flux:button>
        @endif
    </div>
</flux:card>