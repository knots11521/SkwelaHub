<?php

use App\Actions\Schools\RequestSchoolMembership;
use App\Actions\Schools\ReviewSchoolMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('a request creates a pending membership without granting a role', function () {
    $school = School::factory()->create();
    $user = User::factory()->create();

    $membership = (new RequestSchoolMembership)->handle($user, $school, SchoolRole::Student);

    expect($membership->status)->toBe(SchoolMembershipStatus::Pending)
        ->and($membership->requested_role)->toBe(SchoolRole::Student)
        ->and($user->hasRole(SchoolRole::Student->value))->toBeFalse()
        ->and($user->hasApprovedSchoolMembership($school))->toBeFalse();
});

test('a super administrator can approve a school administrator membership and grant its role', function () {
    $school = School::factory()->create();
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $schoolAdmin = User::factory()->create();
    $membership = SchoolMembership::factory()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    (new ReviewSchoolMembership)->approve($superAdmin, $membership);

    expect($membership->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($membership->reviewed_by)->toBe($superAdmin->id)
        ->and($schoolAdmin->hasRole(SchoolRole::SchoolAdmin->value))->toBeTrue()
        ->and($schoolAdmin->hasApprovedSchoolMembership($school))->toBeTrue();
});

test('a school administrator may not approve another school administrator request', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create();
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);
    $request = SchoolMembership::factory()->create([
        'school_id' => $school->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    expect(Gate::forUser($schoolAdmin)->allows('approve', $request))->toBeFalse();
});
