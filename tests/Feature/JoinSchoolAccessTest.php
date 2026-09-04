<?php

use App\Livewire\JoinSchool;
use App\Models\Invite;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('school admin is forbidden from accessing join-school page', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $this->actingAs($schoolAdmin)
        ->get(route('join-school'))
        ->assertForbidden();
});

test('super admin is forbidden from accessing join-school page', function () {
    $superAdmin = User::factory()->create(['school_id' => null]);
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);

    $this->actingAs($superAdmin)
        ->get(route('join-school'))
        ->assertForbidden();
});

test('teacher without approved membership can access join-school page', function () {
    $teacher = User::factory()->create(['school_id' => null]);
    $teacher->assignRole(SchoolRole::Teacher->value);

    $this->actingAs($teacher)
        ->get(route('join-school'))
        ->assertOk();
});

test('teacher with approved membership is redirected to dashboard from join-school page', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $this->actingAs($teacher)
        ->get(route('join-school'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('info');
});

test('student with approved membership can access join-school page', function () {
    $school = School::factory()->create();
    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $this->actingAs($student)
        ->get(route('join-school'))
        ->assertOk();
});

test('parent with approved membership can access join-school page', function () {
    $school = School::factory()->create();
    $parent = User::factory()->create(['school_id' => $school->id]);
    $parent->assignRole(SchoolRole::ParentGuardian->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $parent->id,
        'requested_role' => SchoolRole::ParentGuardian,
    ]);

    $this->actingAs($parent)
        ->get(route('join-school'))
        ->assertOk();
});

test('user with no role can access join-school page', function () {
    $user = User::factory()->create(['school_id' => null]);

    $this->actingAs($user)
        ->get(route('join-school'))
        ->assertOk();
});

test('teacher with approved membership cannot mount JoinSchool component', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    Livewire::actingAs($teacher)
        ->test(JoinSchool::class)
        ->assertRedirect(route('dashboard'));
});

test('school admin cannot mount JoinSchool component', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    Livewire::actingAs($schoolAdmin)
        ->test(JoinSchool::class)
        ->assertForbidden();
});

test('failed invite link for school admin redirects to dashboard instead of join-school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();

    $schoolAdmin = User::factory()->create(['school_id' => $schoolA->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $schoolA->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $invite = Invite::factory()->forSchool($schoolB)->create();

    $this->actingAs($schoolAdmin)
        ->get(route('invite.accept', $invite->link_token))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});

test('failed invite link for student redirects to join-school so they can retry', function () {
    $schoolA = School::factory()->create();
    $student = User::factory()->create(['school_id' => $schoolA->id]);
    $student->assignRole(SchoolRole::Student->value);

    $invite = Invite::factory()->expired()->forSchool($schoolA)->create();

    $this->actingAs($student)
        ->get(route('invite.accept', $invite->link_token))
        ->assertRedirect(route('join-school'))
        ->assertSessionHas('error');
});
