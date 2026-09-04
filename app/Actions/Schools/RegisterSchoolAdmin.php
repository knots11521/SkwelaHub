<?php

namespace App\Actions\Schools;

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterSchoolAdmin
{
    /**
     * @param  array{name: string, email: string, password: string, school_name: string, school_address?: string|null, school_region?: string|null, school_slug?: string|null}  $attributes
     */
    public function handle(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $school = School::query()->create([
                'name' => $attributes['school_name'],
                'slug' => Str::slug($attributes['school_slug'] ?: $attributes['school_name']),
                'address' => $attributes['school_address'] ?? null,
                'region' => $attributes['school_region'] ?? null,
                'is_active' => true,
                'status' => 'active',
            ]);

            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
            ]);

            Role::findOrCreate(SchoolRole::SchoolAdmin->value, 'web');
            $user->assignRole(SchoolRole::SchoolAdmin->value);

            $school->update(['created_by' => $user->id]);

            SchoolMembership::query()->create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'requested_role' => SchoolRole::SchoolAdmin,
                'status' => SchoolMembershipStatus::Approved,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            return $user;
        });
    }
}
