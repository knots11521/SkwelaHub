<?php

namespace App\Livewire\Performance;

use App\Models\GamificationEvent;
use App\Models\PerformanceRecord;
use App\Models\User;
use App\Models\UserAchievement;
use App\SchoolRole;
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

        $isParent = $user->hasRole(SchoolRole::ParentGuardian->value);

        $studentIds = $isParent ? $user->linkedStudentIds() : [$user->id];

        $studentQuery = $user->performanceRecords();
        $gamificationQuery = $user->gamificationEvents();
        $achievementQuery = $user->achievements();

        if ($isParent) {
            $studentQuery = PerformanceRecord::query()->whereIn('student_id', $studentIds);
            $gamificationQuery = GamificationEvent::query()->whereIn('user_id', $studentIds);
            $achievementQuery = UserAchievement::query()->whereIn('user_id', $studentIds);
        }

        return view('livewire.performance.index', [
            'performanceRecords' => $studentQuery
                ->with('learningEnvironment:id,name,section')
                ->latest('recorded_at')
                ->get(),
            'gamificationEvents' => $gamificationQuery
                ->with('learningEnvironment:id,name,section')
                ->latest('awarded_at')
                ->get(),
            'achievements' => $achievementQuery
                ->with('achievement:id,name,description')
                ->latest('awarded_at')
                ->get(),
        ]);
    }
}
