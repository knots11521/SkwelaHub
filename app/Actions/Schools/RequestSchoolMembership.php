<?php

namespace App\Actions\Schools;

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestSchoolMembership
{
    public function handle(User $user, School $school, SchoolRole $role): SchoolMembership
    {
        return DB::transaction(function () use ($user, $school, $role): SchoolMembership {
            $membership = SchoolMembership::query()
                ->whereBelongsTo($school)
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->first();

            if ($membership?->status === SchoolMembershipStatus::Pending) {
                throw ValidationException::withMessages([
                    'requestedRole' => 'You already have a pending request for this school.',
                ]);
            }

            if ($membership?->status === SchoolMembershipStatus::Approved) {
                throw ValidationException::withMessages([
                    'requestedRole' => 'You are already an approved member of this school.',
                ]);
            }

            return SchoolMembership::query()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                ],
                [
                    'requested_role' => $role,
                    'status' => SchoolMembershipStatus::Pending,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ],
            );
        });
    }
}
