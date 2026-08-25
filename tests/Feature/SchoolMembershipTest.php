<?php

use App\Actions\Schools\CreateSchoolUser;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('a school admin directly creates a school-scoped user with immediate access', function (): void {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id, 'role' => SchoolRole::SchoolAdmin]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);

    $teacher = (new CreateSchoolUser)->handle($schoolAdmin, $school, SchoolRole::Teacher, [
        'name' => 'New Teacher',
        'email' => 'new.teacher@example.test',
        'password' => 'password',
    ]);

    expect($teacher->school_id)->toBe($school->id)
        ->and($teacher->role)->toBe(SchoolRole::Teacher)
        ->and($teacher->hasRole(SchoolRole::Teacher->value))->toBeTrue()
        ->and($teacher->hasApprovedSchoolRole($school, SchoolRole::Teacher))->toBeTrue()
        ->and(SchoolMembership::query()->whereBelongsTo($teacher)->value('status'))->toBe(SchoolMembershipStatus::Approved);
});

test('a direct school user is not a member of another school', function (): void {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $student = User::factory()->create(['school_id' => $school->id, 'role' => SchoolRole::Student]);
    $student->assignRole(SchoolRole::Student->value);

    expect($student->can('view', $school))->toBeTrue()
        ->and($student->can('view', $otherSchool))->toBeFalse();
});
