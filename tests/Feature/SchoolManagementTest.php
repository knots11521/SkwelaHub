<?php

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('the school directory is restricted to platform users', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);

    Livewire::actingAs($superAdmin)
        ->test('schools.index')
        ->assertSee('School directory');
});

test('a school administrator cannot manage memberships for another school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $schoolAdmin = User::factory()->create();
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $schoolA->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $this->actingAs($schoolAdmin)
        ->get(route('schools.members', $schoolB))
        ->assertForbidden();
});

test('a super administrator cannot manage a school user directory', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $school = School::factory()->create();

    $this->actingAs($superAdmin)
        ->get(route('schools.members', $school))
        ->assertForbidden();
});

test('a direct school user can view their school while another school user cannot', function () {
    $school = School::factory()->create();
    $approvedUser = User::factory()->create(['school_id' => $school->id]);
    $approvedUser->assignRole(SchoolRole::Student->value);
    $otherSchoolUser = User::factory()->create();
    $otherSchoolUser->assignRole(SchoolRole::Student->value);

    expect($approvedUser->can('view', $school))->toBeTrue()
        ->and($otherSchoolUser->can('view', $school))->toBeFalse();
});
