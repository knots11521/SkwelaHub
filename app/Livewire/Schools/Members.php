<?php

namespace App\Livewire\Schools;

use App\Actions\Schools\ReviewSchoolMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('School members')]
class Members extends Component
{
    public School $school;

    public function mount(School $school): void
    {
        $this->authorize('manageMemberships', $school);

        $this->school = $school;
    }

    public function approve(int $membershipId): void
    {
        $membership = $this->membership($membershipId);

        $this->authorize('approve', $membership);

        (new ReviewSchoolMembership)->approve($this->user(), $membership);

        Flux::toast(variant: 'success', text: 'Membership approved.');
    }

    public function reject(int $membershipId): void
    {
        $membership = $this->membership($membershipId);

        $this->authorize('reject', $membership);

        (new ReviewSchoolMembership)->reject($this->user(), $membership);

        Flux::toast(variant: 'success', text: 'Membership rejected.');
    }

    #[Computed]
    public function memberships(): LengthAwarePaginator
    {
        return SchoolMembership::query()
            ->whereBelongsTo($this->school)
            ->select(['id', 'school_id', 'user_id', 'requested_role', 'status', 'created_at'])
            ->with(['school:id', 'user:id,name,email'])
            ->latest()
            ->paginate(20);
    }

    private function membership(int $membershipId): SchoolMembership
    {
        return SchoolMembership::query()
            ->whereBelongsTo($this->school)
            ->findOrFail($membershipId);
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
