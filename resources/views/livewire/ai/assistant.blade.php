<x-page-section>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><flux:heading size="xl">{{ $learningEnvironment->name }}</flux:heading><flux:text>{{ __('Teacher AI assistant') }}</flux:text></div>
        <div class="flex gap-2"><flux:button :href="route('learning-environments.assignments', $learningEnvironment)" wire:navigate>{{ __('Assignments') }}</flux:button><flux:button :href="route('learning-environments.assessments', $learningEnvironment)" wire:navigate>{{ __('Assessments') }}</flux:button></div>
    </div>

    <flux:callout variant="warning"><flux:heading>{{ __('Teacher review is required') }}</flux:heading><flux:text>{{ __('AI output is a private draft. It cannot be published, graded, or shown to students until you review, approve, and explicitly publish it.') }}</flux:text></flux:callout>

    <flux:card>
        <form wire:submit="generate" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Request a suggestion') }}</flux:heading>
            <flux:select wire:model="kind" :label="__('Suggestion type')"><flux:select.option value="assignment">{{ __('Assignment idea') }}</flux:select.option><flux:select.option value="assessment">{{ __('Assessment or quiz idea') }}</flux:select.option><flux:select.option value="rubric">{{ __('Rubric suggestion') }}</flux:select.option><flux:select.option value="material">{{ __('Learning material idea') }}</flux:select.option></flux:select>
            <flux:textarea wire:model="prompt" :label="__('Teacher request')" placeholder="{{ __('Describe the objective, level, and any constraints.') }}" required />
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Generate private draft') }}</flux:button>
        </form>
    </flux:card>

    <div class="flex flex-col gap-4">
        @forelse($suggestions as $suggestion)
            <flux:card wire:key="suggestion-{{ $suggestion->id }}">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><flux:heading size="lg">{{ $suggestion->title ?: __('Generating suggestion') }}</flux:heading><flux:text>{{ str($suggestion->kind)->headline() }} · {{ __('Requested :date', ['date' => $suggestion->created_at->format('M j, Y g:i A')]) }}</flux:text></div><flux:badge>{{ str($suggestion->status)->headline() }}</flux:badge></div>
                @if($suggestion->status === 'draft')
                    <form wire:submit="saveReview({{ $suggestion->id }})" class="mt-5 flex flex-col gap-4"><flux:input wire:model="titles.{{ $suggestion->id }}" :label="__('Teacher-reviewed title')" required /><flux:textarea wire:model="contents.{{ $suggestion->id }}" :label="__('Teacher-reviewed content')" required /><flux:button type="submit" variant="primary">{{ __('Save teacher review') }}</flux:button></form>
                @else
                    <div class="mt-4 whitespace-pre-line">{{ $suggestion->content }}</div>
                    @if($suggestion->status === 'reviewed')<flux:button class="mt-4" wire:click="approve({{ $suggestion->id }})">{{ __('Approve reviewed suggestion') }}</flux:button>@endif
                    @if($suggestion->status === 'approved')<flux:button class="mt-4" variant="primary" wire:click="publish({{ $suggestion->id }})">{{ __('Publish teacher-approved suggestion') }}</flux:button>@endif
                    @if($suggestion->status === 'published')<flux:text class="mt-4">{{ __('Published by the teacher on :date', ['date' => $suggestion->published_at?->format('M j, Y g:i A')]) }}</flux:text>@endif
                @endif
            </flux:card>
        @empty
            <flux:card><flux:text>{{ __('No AI suggestions have been requested for this environment.') }}</flux:text></flux:card>
        @endforelse
    </div>
</x-page-section>
