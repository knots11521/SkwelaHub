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

test('a super administrator can create a school from the schools page', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);

    Livewire::actingAs($superAdmin)
        ->test('schools.index')
        ->set('schoolName', 'Northview Academy')
        ->call('createSchool')
        ->assertHasNoErrors();

    expect(School::query()->where('name', 'Northview Academy')->exists())->toBeTrue();
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

test('a super administrator can open the school membership page', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);
    $school = School::factory()->create();

    $this->actingAs($superAdmin)
        ->get(route('schools.members', $school))
        ->assertOk();
});

test('an approved member can view their school while a pending member cannot', function () {
    $school = School::factory()->create();
    $approvedUser = User::factory()->create();
    $approvedUser->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $approvedUser->id,
        'requested_role' => SchoolRole::Student,
    ]);
    $pendingUser = User::factory()->create();
    SchoolMembership::factory()->create([
        'school_id' => $school->id,
        'user_id' => $pendingUser->id,
    ]);

    expect($approvedUser->can('view', $school))->toBeTrue()
        ->and($pendingUser->can('view', $school))->toBeFalse();
});
