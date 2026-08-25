<?php

namespace App\Livewire\Schools;

use App\Actions\Schools\CreateSchoolUser;
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

#[Title('School members')]
class Members extends Component
{
    public School $school;

    public string $memberName = '';

    public string $memberEmail = '';

    public string $memberPassword = '';

    public string $memberRole = SchoolRole::Teacher->value;

    public function mount(School $school): void
    {
        $this->authorize('manageMemberships', $school);

        $this->school = $school;
    }

    public function createMember(): void
    {
        $this->authorize('manageMemberships', $this->school);

        $validated = $this->validate([
            'memberName' => ['required', 'string', 'max:255'],
            'memberEmail' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'memberPassword' => ['required', 'string', 'min:8'],
            'memberRole' => ['required', Rule::in([SchoolRole::Teacher->value, SchoolRole::Student->value, SchoolRole::ParentGuardian->value])],
        ]);

        (new CreateSchoolUser)->handle(
            $this->user(),
            $this->school,
            SchoolRole::from($validated['memberRole']),
            ['name' => $validated['memberName'], 'email' => $validated['memberEmail'], 'password' => $validated['memberPassword']],
        );

        $this->reset('memberName', 'memberEmail', 'memberPassword');
        Flux::toast(variant: 'success', text: 'School user created and granted access.');
    }

    #[Computed]
    public function members(): LengthAwarePaginator
    {
        return $this->school->users()
            ->select(['id', 'school_id', 'name', 'email', 'role', 'created_at'])
            ->latest()
            ->paginate(20);
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.schools.members');
    }
}
