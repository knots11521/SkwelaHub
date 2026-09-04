<?php

use App\Livewire\Schools\Members;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('the previous membership request endpoint is unavailable', function (): void {
    $this->get('/membership-requests')->assertNotFound();
});

test('a school admin cannot open another school user management screen', function () {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);

    $this->actingAs($schoolAdmin)
        ->get(route('schools.members', $otherSchool))
        ->assertForbidden();
});

test('school admin can view school members', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $this->actingAs($schoolAdmin)
        ->get(route('schools.members', $school))
        ->assertSuccessful();
});

test('teacher can view school members', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $this->actingAs($teacher)
        ->get(route('schools.members', $school))
        ->assertSuccessful();
});

test('school admin can remove a user from the school and their classroom enrollments', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    $schoolAdminMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $schoolAdmin->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $schoolAdmin->id, 'name' => 'Grade 7']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $schoolAdminMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    $membership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $membership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($schoolAdmin)
        ->test(Members::class, ['school' => $school])
        ->call('removeUserFromSchool', $student->id)
        ->assertHasNoErrors();

    expect(SchoolMembership::query()->where('user_id', $student->id)->where('school_id', $school->id)->exists())->toBeFalse();
    expect(LearningEnvironmentMembership::query()->where('school_membership_id', $membership->id)->exists())->toBeFalse();
});

test('student in a classroom can access the learning hub', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    $membership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $membership->id,
        'learning_environment_id' => $environment->id,
    ]);

    $this->actingAs($student)
        ->get(route('learning-environments.materials', $environment))
        ->assertSuccessful();
});
