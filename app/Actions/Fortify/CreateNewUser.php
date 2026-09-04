<?php

namespace App\Actions\Fortify;

use App\Actions\Schools\RegisterSchoolAdmin;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Invite;
use App\Models\LearningEnvironmentMembership;
use App\Models\ParentStudent;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function create(array $input): User
    {
        $role = $input['role'] ?? null;

        if ($role === SchoolRole::SchoolAdmin->value) {
            return $this->createSchoolAdmin($input);
        }

        if (! empty($input['invite_token'])) {
            return $this->acceptInviteByToken($input);
        }

        if (! empty($input['invite_code'])) {
            return $this->acceptInvite($input);
        }

        return $this->createSelfService($input);
    }

    private function createSchoolAdmin(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:255'],
            'school_region' => ['nullable', 'string', 'max:255'],
            'school_slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique(School::class, 'slug')],
        ])->validate();

        return (new RegisterSchoolAdmin)->handle($input);
    }

    private function createSelfService(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::query()->create([
            'school_id' => null,
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }

    private function acceptInvite(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'invite_code' => ['required', 'string', 'max:100'],
        ])->after(function ($validator) use ($input) {
            $invite = Invite::query()->where('code', $input['invite_code'])->first();

            if (! $invite || ! $invite->isValid()) {
                $validator->errors()->add('invite_code', 'This invite code is invalid or has expired.');

                return;
            }

            $providedRole = $input['role'] ?? null;

            if ($providedRole && $providedRole !== $invite->role->value) {
                $validator->errors()->add('role', 'The selected role does not match this invite code.');
            }

            if ($invite->role === SchoolRole::ParentGuardian && $invite->student_id === null) {
                $validator->errors()->add('invite_code', 'This invite code is not valid for registration.');
            }
        })->validate();

        $invite = Invite::query()->where('code', $input['invite_code'])->firstOrFail();

        $role = $input['role'] ?? $invite->role->value;

        return DB::transaction(function () use ($input, $invite, $role): User {
            $user = User::query()->create([
                'school_id' => $invite->school_id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            Role::findOrCreate($role, 'web');
            $user->assignRole($role);

            SchoolMembership::query()->create([
                'school_id' => $invite->school_id,
                'user_id' => $user->id,
                'requested_role' => SchoolRole::from($role),
                'status' => SchoolMembershipStatus::Approved,
                'reviewed_by' => $invite->created_by,
                'reviewed_at' => now(),
            ]);

            if ($invite->role === SchoolRole::ParentGuardian && $invite->student_id !== null) {
                ParentStudent::query()->create([
                    'parent_id' => $user->id,
                    'student_id' => $invite->student_id,
                ]);
            }

            return $user;
        });
    }

    private function acceptInviteByToken(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'invite_token' => ['required', 'string', 'max:100'],
        ])->after(function ($validator) use ($input) {
            $invite = Invite::query()->where('link_token', $input['invite_token'])->first();

            if (! $invite || ! $invite->isValid()) {
                $validator->errors()->add('invite_token', 'This invite link has expired or been exhausted.');

                return;
            }

            $providedRole = $input['role'] ?? null;

            if ($providedRole && $providedRole !== $invite->role->value) {
                $validator->errors()->add('role', 'The selected role does not match this invite link.');
            }

            if ($invite->role === SchoolRole::ParentGuardian && $invite->student_id === null) {
                $validator->errors()->add('invite_token', 'This invite link is not valid for registration.');
            }
        })->validate();

        $invite = Invite::query()->where('link_token', $input['invite_token'])->firstOrFail();

        $role = $input['role'] ?? $invite->role->value;

        return DB::transaction(function () use ($input, $invite, $role): User {
            $user = User::query()->create([
                'school_id' => $invite->school_id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            Role::findOrCreate($role, 'web');
            $user->assignRole($role);

            $schoolMembership = SchoolMembership::query()->create([
                'school_id' => $invite->school_id,
                'user_id' => $user->id,
                'requested_role' => SchoolRole::from($role),
                'status' => SchoolMembershipStatus::Approved,
                'reviewed_by' => $invite->created_by,
                'reviewed_at' => now(),
            ]);

            if ($invite->learning_environment_id !== null) {
                LearningEnvironmentMembership::query()->create([
                    'school_membership_id' => $schoolMembership->id,
                    'learning_environment_id' => $invite->learning_environment_id,
                ]);
            }

            if ($invite->role === SchoolRole::ParentGuardian && $invite->student_id !== null) {
                ParentStudent::query()->create([
                    'parent_id' => $user->id,
                    'student_id' => $invite->student_id,
                ]);
            }

            return $user;
        });
    }
}
