<?php

use App\Livewire\Schools\Members;
use App\Models\School;
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

test('a school admin can create users only for their school through the management screen', function (): void {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id, 'role' => SchoolRole::SchoolAdmin]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);

    Livewire::actingAs($schoolAdmin)
        ->test(Members::class, ['school' => $school])
        ->set('memberName', 'School Teacher')
        ->set('memberEmail', 'school.teacher@example.test')
        ->set('memberPassword', 'password')
        ->set('memberRole', SchoolRole::Teacher->value)
        ->call('createMember')
        ->assertHasNoErrors();

    $teacher = User::query()->where('email', 'school.teacher@example.test')->firstOrFail();

    expect($teacher->school_id)->toBe($school->id)
        ->and($teacher->role)->toBe(SchoolRole::Teacher);
});

test('a school admin cannot open another school user management screen', function (): void {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id, 'role' => SchoolRole::SchoolAdmin]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);

    $this->actingAs($schoolAdmin)
        ->get(route('schools.members', $otherSchool))
        ->assertForbidden();
});
