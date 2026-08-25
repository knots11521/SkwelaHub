<?php

namespace App\Actions\Schools;

use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReviewSchoolMembership
{
    public function approve(User $reviewer, SchoolMembership $membership): SchoolMembership
    {
        Gate::forUser($reviewer)->authorize('approve', $membership);

        return $this->review($reviewer, $membership, SchoolMembershipStatus::Approved);
    }

    public function reject(User $reviewer, SchoolMembership $membership): SchoolMembership
    {
        Gate::forUser($reviewer)->authorize('reject', $membership);

        return $this->review($reviewer, $membership, SchoolMembershipStatus::Rejected);
    }

    private function review(User $reviewer, SchoolMembership $membership, SchoolMembershipStatus $status): SchoolMembership
    {
        return DB::transaction(function () use ($reviewer, $membership, $status): SchoolMembership {
            $membership = SchoolMembership::query()
                ->lockForUpdate()
                ->findOrFail($membership->id);

            if ($membership->status !== SchoolMembershipStatus::Pending) {
                throw ValidationException::withMessages([
                    'membership' => 'Only pending membership requests can be reviewed.',
                ]);
            }

            $membership->update([
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            if ($status === SchoolMembershipStatus::Approved) {
                $membership->user->assignRole($membership->requested_role->value);
            }

            return $membership;
        });
    }
}
