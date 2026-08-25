<?php

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

test('the V1 roles are seeded for the web guard', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::query()->pluck('name')->all())->toEqualCanonicalizing([
        RoleSeeder::SuperAdmin,
        RoleSeeder::SchoolAdmin,
        RoleSeeder::Teacher,
        RoleSeeder::Student,
        RoleSeeder::ParentGuardian,
    ]);
});

test('newly registered users are identities without roles', function () {
    $user = User::factory()->create();

    expect($user->roles)->toBeEmpty();
});

test('the development seed provides one user for each V1 role', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'super.admin@skwelahub.test')->firstOrFail()->hasRole(RoleSeeder::SuperAdmin))->toBeTrue()
        ->and(User::query()->where('email', 'school.admin@skwelahub.test')->firstOrFail()->hasApprovedSchoolRole(
            School::query()->firstOrFail(),
            SchoolRole::SchoolAdmin,
        ))->toBeTrue()
        ->and(User::query()->where('email', 'teacher@skwelahub.test')->firstOrFail()->hasRole(RoleSeeder::Teacher))->toBeTrue()
        ->and(User::query()->where('email', 'student@skwelahub.test')->firstOrFail()->hasRole(RoleSeeder::Student))->toBeTrue()
        ->and(User::query()->where('email', 'parent@skwelahub.test')->firstOrFail()->hasRole(RoleSeeder::ParentGuardian))->toBeTrue()
        ->and(SchoolMembership::query()->approved()->count())->toBe(7)
        ->and(SchoolMembership::query()->where('status', SchoolMembershipStatus::Pending)->count())->toBe(4)
        ->and(SchoolMembership::query()->approved()->firstOrFail()->status)->toBe(SchoolMembershipStatus::Approved);
});
