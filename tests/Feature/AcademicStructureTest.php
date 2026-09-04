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
    ]);
    $user->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $user->id, 'requested_role' => SchoolRole::Teacher]);

    return $user;
}

test('a teacher is redirected from academic structure to learning environments', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    $this->actingAs($teacher)->get(route('schools.academic', $school))->assertRedirect(route('learning-environments.index'));
});

test('a teacher cannot open or modify another schools structure', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $teacher = academicTeacher($schoolA);
    $subject = Subject::query()->create(['school_id' => $schoolB->id, 'name' => 'Science']);
    $this->actingAs($teacher)->get(route('schools.academic', $schoolB))->assertForbidden();
    expect($teacher->can('update', $subject))->toBeFalse();
});

test('a teacher can create a subject and classroom together from learning environments page', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    Livewire::actingAs($teacher)->test('learning-environments.index')
        ->set('subjectName', 'Physics')
        ->set('environmentName', 'Grade 12')->call('createLearningEnvironment')
        ->assertHasNoErrors();
    expect(Subject::query()->whereBelongsTo($school)->where('name', 'Physics')->exists())->toBeTrue()
        ->and(LearningEnvironment::query()->whereBelongsTo($school)->where('name', 'Grade 12')->exists())->toBeTrue();
});

test('a teacher can create a classroom from learning environments page', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    Livewire::actingAs($teacher)->test('learning-environments.index')
        ->set('subjectName', 'Chemistry')->set('environmentName', 'Grade 8')->call('createLearningEnvironment')
        ->assertHasNoErrors();
    expect(LearningEnvironment::query()->whereBelongsTo($school)->where('name', 'Grade 8')->exists())->toBeTrue();
});

test('a teacher can edit their own classroom from learning environments page', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Biology', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 9']);
    Livewire::actingAs($teacher)->test('learning-environments.index')
        ->call('editLearningEnvironment', $environment->id)
        ->set('environmentName', 'Grade 9 Updated')->set('subjectName', 'Biology Updated')->call('updateLearningEnvironment')
        ->assertHasNoErrors();
    expect($environment->fresh()->name)->toBe('Grade 9 Updated')
        ->and($subject->fresh()->name)->toBe('Biology Updated');
});

test('a teacher can delete their own classroom from learning environments page', function () {
    $school = School::factory()->create();
    $teacher = academicTeacher($school);
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'History', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 10']);
    Livewire::actingAs($teacher)->test('learning-environments.index')
        ->call('deleteLearningEnvironment', $environment->id);
    expect(LearningEnvironment::query()->find($environment->id))->toBeNull();
});

test('school admin can still access academic structure page', function () {
    $school = School::factory()->create();
    $admin = User::factory()->create(['school_id' => $school->id]);
    $admin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $admin->id, 'requested_role' => SchoolRole::SchoolAdmin]);
    $this->actingAs($admin)->get(route('schools.academic', $school))->assertSuccessful();
});
