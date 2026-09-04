<?php

use App\Livewire\Assessments\Results;
use App\Livewire\Assessments\Take as AssessmentTake;
use App\Livewire\Assignments\Index as AssignmentIndex;
use App\Livewire\Assignments\Submissions;
use App\Livewire\Performance\Index as PerformanceIndex;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\GamificationEvent;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\PerformanceRecord;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserAchievement;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Mathematics']);
    $this->environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'A']);
    $this->otherEnvironment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'B']);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->teacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $teacherMembership->id, 'learning_environment_id' => $this->environment->id]);

    $this->otherTeacher = User::factory()->create();
    $this->otherTeacher->assignRole(SchoolRole::Teacher->value);
    $otherTeacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->otherTeacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $otherTeacherMembership->id, 'learning_environment_id' => $this->otherEnvironment->id]);

    $this->student = User::factory()->create();
    $this->student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->student->id, 'requested_role' => SchoolRole::Student]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $studentMembership->id, 'learning_environment_id' => $this->environment->id]);

    $this->parent = User::factory()->create();
    $this->parent->assignRole(SchoolRole::ParentGuardian->value);
});

test('teacher evaluation creates a derived performance record and assignment completion awards separate points', function (): void {
    $assignment = Assignment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Integer reflection',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->actingAs($this->student);
    Livewire::test(AssignmentIndex::class, ['learningEnvironment' => $this->environment])
        ->set("submissionContents.{$assignment->id}", 'My integer explanation.')
        ->call('submit', $assignment->id);

    $submission = AssignmentSubmission::query()->firstOrFail();
    expect(GamificationEvent::query()->where('user_id', $this->student->id)->sum('points'))->toBe(10)
        ->and(UserAchievement::query()->where('user_id', $this->student->id)->count())->toBe(1)
        ->and(PerformanceRecord::query()->count())->toBe(0);

    $this->actingAs($this->teacher);
    Livewire::test(Submissions::class, ['assignment' => $assignment])
        ->set("scores.{$submission->id}", '84')
        ->set("feedback.{$submission->id}", 'Clear reasoning. Check your sign on the final step.')
        ->call('evaluate', $submission->id);

    $record = PerformanceRecord::query()->firstOrFail();
    expect($submission->refresh()->evaluation_status)->toBe('evaluated')
        ->and((float) $submission->score)->toBe(84.0)
        ->and($submission->feedback)->toBe('Clear reasoning. Check your sign on the final step.')
        ->and($record->source_type)->toBe(AssignmentSubmission::class)
        ->and($record->source_id)->toBe($submission->id)
        ->and((float) $record->score)->toBe(84.0)
        ->and($this->student->can('update', $record))->toBeFalse()
        ->and($this->parent->can('view', $record))->toBeFalse()
        ->and($this->otherTeacher->can('evaluate', $submission))->toBeFalse();

    $this->actingAs($this->student);
    Livewire::test(PerformanceIndex::class)->assertSee('Academic Performance')->assertSee('Engagement Points');
});

test('assessment completion creates a result and performance record, while evaluation remains teacher-only', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Integers check-in',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $question = $assessment->questions()->create(['prompt' => 'What is -2 + 5?', 'options' => ['-7', '3'], 'correct_option' => 1]);

    $this->actingAs($this->student);
    Livewire::test(AssessmentTake::class, ['assessment' => $assessment])
        ->set("answers.{$question->id}", 1)
        ->call('submit');

    $attempt = AssessmentAttempt::query()->firstOrFail();
    $record = PerformanceRecord::query()->firstOrFail();
    expect((float) $attempt->score)->toBe(100.0)
        ->and($record->source_type)->toBe(AssessmentAttempt::class)
        ->and((float) $record->score)->toBe(100.0)
        ->and(GamificationEvent::query()->where('source_id', $attempt->id)->exists())->toBeTrue();

    $this->actingAs($this->otherTeacher);
    Livewire::test(Results::class, ['assessment' => $assessment])->assertForbidden();

    $this->actingAs($this->teacher);
    Livewire::test(Results::class, ['assessment' => $assessment])
        ->set("scores.{$attempt->id}", '92')
        ->set("feedback.{$attempt->id}", 'Good work. Review the number line example.')
        ->call('evaluate', $attempt->id);

    expect((float) $attempt->refresh()->score)->toBe(92.0)
        ->and($attempt->evaluation_status)->toBe('evaluated')
        ->and((float) $record->refresh()->score)->toBe(92.0)
        ->and($this->parent->can('update', $record))->toBeFalse();
});
