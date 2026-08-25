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

function academicTeacher(School $school): User
{
    $user = User::factory()->create([
        'school_id' => $school->id,
        'role' => SchoolRole::Teacher,
    ]);
    $user->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $user->id, 'requested_role' => SchoolRole::Teacher]);

    return $user;
}

test('a teacher can create school-owned subjects and classes', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    Livewire::actingAs($teacher)->test('academic.manage', ['school' => $school])
        ->set('subjectName', 'Mathematics')->set('subjectCode', 'MATH-101')->call('createSubject')
        ->set('subjectId', Subject::query()->firstOrFail()->id)->set('environmentName', 'Grade 7')->set('section', 'A')->call('createLearningEnvironment')->assertHasNoErrors();
    expect(Subject::query()->whereBelongsTo($school)->where('code', 'MATH-101')->exists())->toBeTrue()
        ->and(LearningEnvironment::query()->whereBelongsTo($school)->where('name', 'Grade 7')->exists())->toBeTrue();
});

test('a teacher cannot open or modify another schools structure', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $teacher = academicTeacher($schoolA);
    $subject = Subject::query()->create(['school_id' => $schoolB->id, 'name' => 'Science', 'code' => 'SCI-101']);
    $this->actingAs($teacher)->get(route('schools.academic', $schoolB))->assertForbidden();
    expect($teacher->can('update', $subject))->toBeFalse();
});
