<x-page-section max-width="4xl">
    <div>
        <flux:heading size="xl">{{ $learningEnvironment->name }}</flux:heading>
        <flux:text>{{ __('Learning materials') }}</flux:text>
    </div>
    @if ($this->canManage())
        <flux:card>
            <form wire:submit="create" class="flex flex-col gap-4">
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:select wire:model.live="type" :label="__('Material type')">
                    <flux:select.option value="text">{{ __('Text lesson') }}</flux:select.option>
                    <flux:select.option value="link">{{ __('Link') }}</flux:select.option>
                    <flux:select.option value="upload">{{ __('Upload') }}</flux:select.option>
                </flux:select>
                @if ($type === 'text')
                    <flux:textarea wire:model="content" :label="__('Lesson content')" />
                @elseif($type === 'link')
                <flux:input wire:model="url" type="url" :label="__('URL')" />@else
                    <flux:input wire:model="upload" type="file" :label="__('File')" />
                @endif
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    {{ __('Publish material') }}</flux:button>
            </form>
        </flux:card>
    @endif
    <div class="flex flex-col gap-4">
        @forelse($materials as $material)
            <flux:card wire:key="material-{{ $material->id }}">
                <div class="flex justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $material->title }}</flux:heading>
                        <flux:text>{{ $material->author->name }} · {{ str($material->type)->headline() }}</flux:text>
                    </div>
                    @can('delete', $material)
                        <flux:button size="sm" variant="danger" wire:click="delete({{ $material->id }})">
                            {{ __('Delete') }}</flux:button>
                    @endcan
                </div>
                @if ($material->type === 'text')
                    <div class="mt-4 whitespace-pre-line">{{ $material->content }}</div>
                @elseif($material->type === 'link')
                    <flux:link class="mt-4" :href="$material->url" target="_blank">{{ $material->url }}</flux:link>
                @else<flux:link class="mt-4" :href="Storage::disk('public')->url($material->file_path)"
                        target="_blank">{{ __('Download file') }}</flux:link>
                @endif
            </flux:card>
        @empty<flux:card>
                <flux:text>{{ __('No materials have been published yet.') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</x-page-section>
