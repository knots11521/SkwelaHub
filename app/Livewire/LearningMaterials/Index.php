<?php

namespace App\Livewire\LearningMaterials;

use App\Livewire\Concerns\ManagesLearningContent;
use App\Models\LearningEnvironment;
use App\Models\LearningMaterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Learning materials')]
class Index extends Component
{
    use ManagesLearningContent;
    use WithFileUploads;

    public LearningEnvironment $learningEnvironment;

    public string $title = '';

    public string $type = 'text';

    public string $content = '';

    public string $url = '';

    public $upload;

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('viewLearningContent', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function create(): void
    {
        $this->authorize('create', [LearningMaterial::class, $this->learningEnvironment]);
        $data = $this->validate(['title' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::in(['text', 'link', 'upload'])], 'content' => [Rule::requiredIf($this->type === 'text'), 'nullable', 'string'], 'url' => [Rule::requiredIf($this->type === 'link'), 'nullable', 'url'], 'upload' => [Rule::requiredIf($this->type === 'upload'), 'nullable', 'file', 'max:10240']]);
        LearningMaterial::query()->create(['learning_environment_id' => $this->learningEnvironment->id, 'created_by' => Auth::id(), 'title' => $data['title'], 'type' => $data['type'], 'content' => $data['content'] ?: null, 'url' => $data['url'] ?: null, 'file_path' => $this->type === 'upload' ? $this->upload->store('learning-materials', 'public') : null]);
        $this->reset('title', 'content', 'url', 'upload');
        Flux::toast(variant: 'success', text: 'Material published.');
    }

    public function delete(int $id): void
    {
        $material = $this->learningEnvironment->materials()->findOrFail($id);
        $this->authorize('delete', $material);
        $material->delete();
        Flux::toast(text: 'Material removed.');
    }

    public function render()
    {
        return view('livewire.learning-materials.index', ['materials' => $this->learningEnvironment->materials()->with('author:id,name')->latest()->get()]);
    }
}
