<?php

namespace App\Actions\Schools;

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateSchoolUser
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function handle(User $creator, School $school, SchoolRole $role, array $attributes): User
    {
        return DB::transaction(function () use ($creator, $school, $role, $attributes): User {
            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'role' => $role,
            ]);

            Role::findOrCreate($role->value, 'web');
            $user->assignRole($role->value);

            SchoolMembership::query()->create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'requested_role' => $role,
                'status' => SchoolMembershipStatus::Approved,
                'reviewed_by' => $creator->id,
                'reviewed_at' => now(),
            ]);

            return $user;
        });
    }
}
