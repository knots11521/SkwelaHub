<?php

use App\Livewire\Assessments\Index;
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
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Science', 'code' => 'SCI-7']);
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
    $this->actingAs($this->teacher);
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->set('title', 'Science check-in')
        ->set('questionPrompt', 'Which planet is known as the Red Planet?')
        ->set('questionOptions', "Earth\nMars")
        ->set('correctOption', 2)
        ->call('create');

    $assessment = Assessment::query()->with('questions')->firstOrFail();
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->call('publish', $assessment->id);

    $this->actingAs($this->student);
    Livewire::test(Index::class, ['learningEnvironment' => $this->environment])
        ->set("answers.{$assessment->questions->first()->id}", 1)
        ->call('submit', $assessment->id);

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
