<?php

namespace App\Livewire\Schools;

use App\Models\School;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Schools')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', School::class);
    }

    #[Computed]
    public function schools(): LengthAwarePaginator
    {
        return School::query()
            ->active()
            ->select(['id', 'name', 'slug', 'description', 'region'])
            ->latest()
            ->paginate(12);
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.schools.index');
    }
}
