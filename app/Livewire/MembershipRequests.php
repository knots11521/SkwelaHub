<?php

namespace App\Livewire;

use App\Actions\Schools\ReviewSchoolMembership;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Membership requests')]
class MembershipRequests extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', SchoolMembership::class);
    }

    public function approve(int $membershipId): void
    {
        $membership = $this->membership($membershipId);

        $this->authorize('approve', $membership);

        (new ReviewSchoolMembership)->approve($this->user(), $membership);

        Flux::toast(variant: 'success', text: 'Membership request approved.');
    }

    public function reject(int $membershipId): void
    {
        $membership = $this->membership($membershipId);

        $this->authorize('reject', $membership);

        (new ReviewSchoolMembership)->reject($this->user(), $membership);

        Flux::toast(text: 'Membership request rejected.');
    }

    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        $user = $this->user();
        $schoolAdminSchoolIds = $this->schoolIdsFor($user, SchoolRole::SchoolAdmin);
        $teacherSchoolIds = $this->schoolIdsFor($user, SchoolRole::Teacher);

        return SchoolMembership::query()
            ->where('status', SchoolMembershipStatus::Pending)
            ->where(function (Builder $query) use ($user, $schoolAdminSchoolIds, $teacherSchoolIds): void {
                if ($user->hasRole(RoleSeeder::SuperAdmin)) {
                    $query->orWhere('requested_role', SchoolRole::SchoolAdmin->value);
                }

                if ($schoolAdminSchoolIds !== []) {
                    $query->orWhere(function (Builder $query) use ($schoolAdminSchoolIds): void {
                        $query->whereIn('school_id', $schoolAdminSchoolIds)
                            ->where('requested_role', SchoolRole::Teacher->value);
                    });
                }

                if ($teacherSchoolIds !== []) {
                    $query->orWhere(function (Builder $query) use ($teacherSchoolIds): void {
                        $query->whereIn('school_id', $teacherSchoolIds)
                            ->whereIn('requested_role', [SchoolRole::Student->value, SchoolRole::ParentGuardian->value]);
                    });
                }
            })
            ->with(['school:id,name', 'user:id,name,email'])
            ->latest()
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.membership-requests');
    }

    /**
     * @return list<int>
     */
    private function schoolIdsFor(User $user, SchoolRole $role): array
    {
        if (! $user->hasRole($role->value)) {
            return [];
        }

        return $user->schoolMemberships()
            ->approved()
            ->where('requested_role', $role->value)
            ->pluck('school_id')
            ->all();
    }

    private function membership(int $membershipId): SchoolMembership
    {
        return SchoolMembership::query()->findOrFail($membershipId);
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
