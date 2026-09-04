<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ $learningEnvironment->name }}</flux:heading>
            <flux:text>{{ __('Assignments') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('learning-environments.materials', $learningEnvironment)" wire:navigate>{{ __('Materials') }}</flux:button>
            <flux:button :href="route('learning-environments.assessments', $learningEnvironment)" wire:navigate>{{ __('Assessments') }}</flux:button>
        </div>
    </div>

    @if ($this->canManage())
        <flux:card>
            <form wire:submit="create" class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Create assignment') }}</flux:heading>
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:textarea wire:model="instructions" :label="__('Instructions')" />
                <flux:input wire:model="dueAt" type="datetime-local" :label="__('Due date (optional)')" />
                <flux:button type="submit" variant="primary">{{ __('Save draft') }}</flux:button>
            </form>
        </flux:card>
    @endif

    <div class="flex flex-col gap-4">
        @forelse($assignments as $assignment)
            <flux:card wire:key="assignment-{{ $assignment->id }}">
                <div class="flex flex-col justify-between gap-4 sm:flex-row">
                    <div>
                        <div class="flex items-center gap-2"><flux:heading size="lg">{{ $assignment->title }}</flux:heading><flux:badge>{{ str($assignment->status)->headline() }}</flux:badge></div>
                        <flux:text>{{ __('By :name', ['name' => $assignment->author->name]) }}@if($assignment->due_at) · {{ __('Due :date', ['date' => $assignment->due_at->format('M j, Y g:i A')]) }}@endif</flux:text>
                    </div>
                    @if ($this->canManage())
                        <div class="flex gap-2">
                            @if ($assignment->status === 'draft')<flux:button wire:click="publish({{ $assignment->id }})" variant="primary">{{ __('Publish') }}</flux:button>@endif
                            <flux:button :href="route('assignments.submissions', $assignment)" wire:navigate>{{ __('Submissions') }}</flux:button>
                        </div>
                    @endif
                </div>
                @if ($assignment->instructions)<div class="mt-4 whitespace-pre-line">{{ $assignment->instructions }}</div>@endif
                @if (! $this->canManage())
                    @if ($submissions->has($assignment->id))
                        <flux:text class="mt-4">{{ __('Submitted (attempt :attempt)', ['attempt' => $submissions[$assignment->id]->attempt]) }}</flux:text>
                        @if ($submissions[$assignment->id]->evaluation_status === 'evaluated')
                            <flux:card class="mt-4 bg-teal-50 dark:bg-teal-950"><flux:heading>{{ __('Score: :score%', ['score' => $submissions[$assignment->id]->score]) }}</flux:heading>@if($submissions[$assignment->id]->feedback)<flux:text class="mt-2">{{ $submissions[$assignment->id]->feedback }}</flux:text>@endif</flux:card>
                        @endif
                    @else
                        <form wire:submit="submit({{ $assignment->id }})" class="mt-4 flex flex-col gap-3">
                            <flux:textarea wire:model="submissionContents.{{ $assignment->id }}" :label="__('Your work')" required />
                            <flux:button type="submit" variant="primary">{{ __('Submit work') }}</flux:button>
                        </form>
                    @endif
                @endif
            </flux:card>
        @empty
            <flux:card><flux:text>{{ $this->canManage() ? __('No assignments have been created yet.') : __('No assignments are available yet.') }}</flux:text></flux:card>
        @endforelse
    </div>
</x-page-section>
