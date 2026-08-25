<?php

use App\Actions\Schools\ReviewSchoolMembership;
use App\Livewire\MembershipRequests;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->school = School::factory()->create();
});

test('membership approval follows the super admin, school admin, and teacher chain', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $schoolAdmin = User::factory()->create();
    $schoolAdminRequest = SchoolMembership::factory()->create([
        'school_id' => $this->school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    (new ReviewSchoolMembership)->approve($superAdmin, $schoolAdminRequest);

    $teacher = User::factory()->create();
    $teacherRequest = SchoolMembership::factory()->create([
        'school_id' => $this->school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    (new ReviewSchoolMembership)->approve($schoolAdmin, $teacherRequest);

    $student = User::factory()->create();
    $studentRequest = SchoolMembership::factory()->create([
        'school_id' => $this->school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);
    $parent = User::factory()->create();
    $parentRequest = SchoolMembership::factory()->create([
        'school_id' => $this->school->id,
        'user_id' => $parent->id,
        'requested_role' => SchoolRole::ParentGuardian,
    ]);

    (new ReviewSchoolMembership)->approve($teacher, $studentRequest);
    (new ReviewSchoolMembership)->approve($teacher, $parentRequest);

    expect($schoolAdminRequest->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($teacherRequest->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($studentRequest->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($parentRequest->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($schoolAdmin->hasRole(SchoolRole::SchoolAdmin->value))->toBeTrue()
        ->and($teacher->hasRole(SchoolRole::Teacher->value))->toBeTrue()
        ->and($student->hasRole(SchoolRole::Student->value))->toBeTrue()
        ->and($parent->hasRole(SchoolRole::ParentGuardian->value))->toBeTrue();
});

test('reviewers cannot bypass their assigned membership level', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $schoolAdmin = User::factory()->create();
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);
    $teacher = User::factory()->create();
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $teacherRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'requested_role' => SchoolRole::Teacher]);
    $studentRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'requested_role' => SchoolRole::Student]);
    $schoolAdminRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'requested_role' => SchoolRole::SchoolAdmin]);

    expect(Gate::forUser($superAdmin)->allows('approve', $teacherRequest))->toBeFalse()
        ->and(Gate::forUser($schoolAdmin)->allows('approve', $studentRequest))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('approve', $teacherRequest))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('approve', $schoolAdminRequest))->toBeFalse();
});

test('each reviewer sees and can approve only their assigned request type', function (): void {
    $superAdmin = User::factory()->create(['name' => 'Platform reviewer']);
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $schoolAdmin = User::factory()->create(['name' => 'School reviewer']);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $this->school->id, 'user_id' => $schoolAdmin->id, 'requested_role' => SchoolRole::SchoolAdmin]);
    $teacher = User::factory()->create(['name' => 'Teacher reviewer']);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $this->school->id, 'user_id' => $teacher->id, 'requested_role' => SchoolRole::Teacher]);

    $adminRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'user_id' => User::factory()->create(['name' => 'School admin applicant']), 'requested_role' => SchoolRole::SchoolAdmin]);
    $teacherRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'user_id' => User::factory()->create(['name' => 'Teacher applicant']), 'requested_role' => SchoolRole::Teacher]);
    $studentRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'user_id' => User::factory()->create(['name' => 'Student applicant']), 'requested_role' => SchoolRole::Student]);
    $parentRequest = SchoolMembership::factory()->create(['school_id' => $this->school->id, 'user_id' => User::factory()->create(['name' => 'Parent applicant']), 'requested_role' => SchoolRole::ParentGuardian]);

    Livewire::actingAs($superAdmin)
        ->test(MembershipRequests::class)
        ->assertSee('School admin applicant')
        ->assertDontSee('Teacher applicant');

    Livewire::actingAs($schoolAdmin)
        ->test(MembershipRequests::class)
        ->assertSee('Teacher applicant')
        ->assertDontSee('Student applicant');

    Livewire::actingAs($teacher)
        ->test(MembershipRequests::class)
        ->assertSee('Student applicant')
        ->assertSee('Parent applicant')
        ->assertDontSee('Teacher applicant')
        ->call('approve', $studentRequest->id);

    expect($studentRequest->refresh()->status)->toBe(SchoolMembershipStatus::Approved)
        ->and($adminRequest->refresh()->status)->toBe(SchoolMembershipStatus::Pending)
        ->and($teacherRequest->refresh()->status)->toBe(SchoolMembershipStatus::Pending)
        ->and($parentRequest->refresh()->status)->toBe(SchoolMembershipStatus::Pending);
});

test('students and parents cannot open the request review screen', function (): void {
    $student = User::factory()->create();
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $this->school->id, 'user_id' => $student->id, 'requested_role' => SchoolRole::Student]);

    $this->actingAs($student)
        ->get(route('membership-requests.index'))
        ->assertForbidden();
});
