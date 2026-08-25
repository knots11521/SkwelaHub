<?php

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function academicAdmin(School $school): User
{
    $user = User::factory()->create();
    $user->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $user->id, 'requested_role' => SchoolRole::SchoolAdmin]);

    return $user;
}

test('a school administrator can create school-owned subjects and classes', function () {
    $school = School::factory()->create();
    $admin = academicAdmin($school);
    Livewire::actingAs($admin)->test('academic.manage', ['school' => $school])
        ->set('subjectName', 'Mathematics')->set('subjectCode', 'MATH-101')->call('createSubject')
        ->set('subjectId', Subject::query()->firstOrFail()->id)->set('environmentName', 'Grade 7')->set('section', 'A')->call('createLearningEnvironment')->assertHasNoErrors();
    expect(Subject::query()->whereBelongsTo($school)->where('code', 'MATH-101')->exists())->toBeTrue()
        ->and(LearningEnvironment::query()->whereBelongsTo($school)->where('name', 'Grade 7')->exists())->toBeTrue();
});

test('a school administrator cannot open or modify another schools structure', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $admin = academicAdmin($schoolA);
    $subject = Subject::query()->create(['school_id' => $schoolB->id, 'name' => 'Science', 'code' => 'SCI-101']);
    $this->actingAs($admin)->get(route('schools.academic', $schoolB))->assertForbidden();
    expect($admin->can('update', $subject))->toBeFalse();
});
