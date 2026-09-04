<?php

use App\Livewire\Assessments\Index;
use App\Livewire\Assessments\Take;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
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
    $school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Science']);
    $this->environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'A']);
    $this->otherEnvironment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'B']);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->teacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $teacherMembership->id, 'learning_environment_id' => $this->environment->id]);

    $this->student = User::factory()->create();
    $this->student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->student->id, 'requested_role' => SchoolRole::Student]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $studentMembership->id, 'learning_environment_id' => $this->environment->id]);
});

test('an enrolled student completes a published assessment and receives a result', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Science check-in',
        'status' => 'draft',
    ]);
    $assessment->questions()->create([
        'prompt' => 'Which planet is known as the Red Planet?',
        'options' => ['Earth', 'Mars'],
        'correct_option' => 1,
    ]);
    $assessment->update(['status' => 'published', 'published_at' => now()]);
    $assessment->load('questions');

    $this->actingAs($this->student);
    Livewire::test(Take::class, ['assessment' => $assessment])
        ->set("answers.{$assessment->questions->first()->id}", 1)
        ->call('submit');

    $attempt = AssessmentAttempt::query()->where('assessment_id', $assessment->id)->firstOrFail();
    expect($attempt->student_id)->toBe($this->student->id)
        ->and((float) $attempt->score)->toBe(100.0)
        ->and($attempt->result_available_at)->not->toBeNull()
        ->and($attempt->responses)->toHaveCount(1)
        ->and($attempt->responses->first()->is_correct)->toBeTrue();
});

test('assessment attempts and aggregate results stay inside the teacher classroom', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->otherEnvironment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Other class assessment',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $assessment->questions()->create(['prompt' => 'Question?', 'options' => ['Yes', 'No'], 'correct_option' => 0]);

    $this->actingAs($this->student);
    Livewire::test(Index::class, ['learningEnvironment' => $this->otherEnvironment])
        ->assertForbidden();

    expect($this->teacher->can('viewResults', $assessment))->toBeFalse();
});

test('the assessment author who is a teacher can delete their own assessment', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Deletable check-in',
        'status' => 'draft',
    ]);

    expect($this->teacher->can('delete', $assessment))->toBeTrue();

    $this->actingAs($this->teacher);
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->call('delete', $assessment->id);

    expect(Assessment::query()->find($assessment->id))->toBeNull();
});

test('a student cannot delete an assessment', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Not mine to delete',
        'status' => 'draft',
    ]);

    expect($this->student->can('delete', $assessment))->toBeFalse();
});

test('a student can retake a published assessment creating a second attempt without duplicates', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Retakeable check-in',
        'status' => 'draft',
    ]);
    $assessment->questions()->createMany([
        ['prompt' => 'Q1?', 'options' => ['A', 'B'], 'correct_option' => 0],
        ['prompt' => 'Q2?', 'options' => ['Yes', 'No'], 'correct_option' => 1],
    ]);
    $assessment->update(['status' => 'published', 'published_at' => now()]);
    $assessment->load('questions');

    $this->actingAs($this->student);

    // First attempt
    Livewire::test(Take::class, ['assessment' => $assessment])
        ->set("answers.{$assessment->questions[0]->id}", 0)
        ->set("answers.{$assessment->questions[1]->id}", 1)
        ->call('submit');

    $firstAttempt = AssessmentAttempt::query()->where('assessment_id', $assessment->id)->orderBy('attempt')->firstOrFail();
    expect($firstAttempt->attempt)->toBe(1)
        ->and((float) $firstAttempt->score)->toBe(100.0);

    // Second attempt (retake)
    Livewire::test(Take::class, ['assessment' => $assessment])
        ->set("answers.{$assessment->questions[0]->id}", 1)
        ->set("answers.{$assessment->questions[1]->id}", 0)
        ->call('submit');

    $attempts = AssessmentAttempt::query()->where('assessment_id', $assessment->id)->orderBy('attempt')->get();
    expect($attempts)->toHaveCount(2)
        ->and($attempts[0]->attempt)->toBe(1)
        ->and($attempts[1]->attempt)->toBe(2)
        ->and((float) $attempts[1]->score)->toBe(0.0);
});
