<?php

namespace App\Livewire\Performance;

use App\Models\PerformanceRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My performance')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', PerformanceRecord::class);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('livewire.performance.index', [
            'performanceRecords' => $user->performanceRecords()
                ->with('learningEnvironment:id,name,section')
                ->latest('recorded_at')
                ->get(),
            'gamificationEvents' => $user->gamificationEvents()
                ->with('learningEnvironment:id,name,section')
                ->latest('awarded_at')
                ->get(),
            'achievements' => $user->achievements()
                ->with('achievement:id,name,description')
                ->latest('awarded_at')
                ->get(),
        ]);
    }
}
