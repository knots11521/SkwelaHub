<?php

namespace App\Livewire\SuperAdmin;

use App\Models\School;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('School Management')]
class Schools extends Component
{
    public string $search = '';

    public function render(): View
    {
        $schools = School::query()
            ->with(['creator', 'memberships'])
            ->withCount(['memberships', 'subjects', 'learningEnvironments'])
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('slug', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(15);

        return view('livewire.super-admin.schools', [
            'schools' => $schools,
        ]);
    }

    public function suspend(int $id): void
    {
        $school = School::query()->findOrFail($id);
        $school->update(['status' => 'suspended']);

        Flux::toast(variant: 'success', text: __('School :name has been suspended.', ['name' => $school->name]));
    }

    public function reactivate(int $id): void
    {
        $school = School::query()->findOrFail($id);
        $school->update(['status' => 'active']);

        Flux::toast(variant: 'success', text: __('School :name has been reactivated.', ['name' => $school->name]));
    }

    public function markAsReviewed(int $id): void
    {
        $school = School::query()->findOrFail($id);
        $school->update(['reviewed' => true]);

        Flux::toast(variant: 'success', text: __('School :name marked as reviewed.', ['name' => $school->name]));
    }

    public function unmarkAsReviewed(int $id): void
    {
        $school = School::query()->findOrFail($id);
        $school->update(['reviewed' => false]);

        Flux::toast(variant: 'success', text: __('School :name unmarked as reviewed.', ['name' => $school->name]));
    }
}
