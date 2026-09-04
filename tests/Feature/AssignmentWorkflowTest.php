<?php

use App\Livewire\Assignments\Index;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
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
    $this->school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $this->school->id, 'name' => 'Math']);
    $this->environment = LearningEnvironment::query()->create(['school_id' => $this->school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'A']);
    $this->otherEnvironment = LearningEnvironment::query()->create(['school_id' => $this->school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'B']);

    $this->teacher = User::factory()->create(['school_id' => $this->school->id]);
    $this->teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $this->school->id, 'user_id' => $this->teacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $teacherMembership->id, 'learning_environment_id' => $this->environment->id]);

    $this->student = User::factory()->create();
    $this->student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create(['school_id' => $this->school->id, 'user_id' => $this->student->id, 'requested_role' => SchoolRole::Student]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $studentMembership->id, 'learning_environment_id' => $this->environment->id]);
});

test('a teacher can publish an assignment and an enrolled student submits as themselves', function (): void {
    $this->actingAs($this->teacher);

    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->set('title', 'Integers reflection')
        ->set('instructions', 'Explain your answer.')
        ->call('create');

    $assignment = Assignment::query()->firstOrFail();
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->call('publish', $assignment->id);

    $this->actingAs($this->student);
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->set("submissionContents.{$assignment->id}", 'My explanation of the integer problem.')
        ->call('submit', $assignment->id);

    $this->assertDatabaseHas('assignment_submissions', [
        'assignment_id' => $assignment->id,
        'learning_environment_id' => $this->environment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'status' => 'submitted',
    ]);
});

test('a student cannot submit to a classroom they are not enrolled in and teachers cannot view foreign submissions', function (): void {
    $assignment = Assignment::query()->create([
        'learning_environment_id' => $this->otherEnvironment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Other class work',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->actingAs($this->student);
    Livewire::test(Index::class, ['learningEnvironment' => $this->otherEnvironment])
        ->assertForbidden();

    $foreignSubmission = AssignmentSubmission::query()->create([
        'assignment_id' => $assignment->id,
        'learning_environment_id' => $this->otherEnvironment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'status' => 'submitted',
        'content' => 'Private work',
        'submitted_at' => now(),
    ]);

    expect($this->teacher->can('viewSubmissions', $assignment))->toBeFalse()
        ->and($this->teacher->can('view', $foreignSubmission))->toBeFalse();
});
