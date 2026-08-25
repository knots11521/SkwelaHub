<?php

namespace App\Livewire\Schools;

use App\Actions\Schools\CreateSchool;
use App\Actions\Schools\RequestSchoolMembership;
use App\Models\School;
use App\Models\User;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Schools')]
class Index extends Component
{
    public bool $showSchoolForm = false;

    public string $schoolName = '';

    public string $requestedRole = '';

    public function mount(): void
    {
        $this->requestedRole = SchoolRole::Student->value;
    }

    public function createSchool(): void
    {
        $this->authorize('create', School::class);

        $validated = $this->validate([
            'schoolName' => ['required', 'string', 'max:255'],
        ]);

        (new CreateSchool)->handle($this->user(), ['name' => $validated['schoolName']]);

        $this->reset('schoolName', 'showSchoolForm');

        Flux::toast(variant: 'success', text: 'School created.');
    }

    public function requestMembership(int $schoolId): void
    {
        $validated = $this->validate([
            'requestedRole' => ['required', Rule::enum(SchoolRole::class)],
        ]);

        $school = School::query()->active()->findOrFail($schoolId);

        (new RequestSchoolMembership)->handle(
            $this->user(),
            $school,
            SchoolRole::from($validated['requestedRole']),
        );

        Flux::toast(variant: 'success', text: 'Your membership request is pending review.');
    }

    #[Computed]
    public function schools(): LengthAwarePaginator
    {
        return School::query()
            ->active()
            ->select(['id', 'name', 'slug', 'description'])
            ->with(['memberships' => fn ($query) => $query
                ->select(['id', 'school_id', 'user_id', 'requested_role', 'status'])
                ->whereBelongsTo($this->user())])
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function canCreateSchools(): bool
    {
        return $this->user()->can('create', School::class);
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
