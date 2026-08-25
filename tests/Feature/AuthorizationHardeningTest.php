<?php

use App\Models\AiSuggestion;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->foreignSchool = School::factory()->create(['name' => 'Private Foreign School']);
    $subject = Subject::query()->create(['school_id' => $this->foreignSchool->id, 'name' => 'Private Mathematics', 'code' => 'PRIVATE-MATH']);
    $this->foreignEnvironment = LearningEnvironment::query()->create(['school_id' => $this->foreignSchool->id, 'subject_id' => $subject->id, 'name' => 'Private Class', 'section' => 'A']);
    $this->foreignAuthor = User::factory()->create();
    $this->assignment = Assignment::query()->create(['learning_environment_id' => $this->foreignEnvironment->id, 'created_by' => $this->foreignAuthor->id, 'title' => 'Private assignment', 'status' => 'published', 'published_at' => now()]);
    $this->assessment = Assessment::query()->create(['learning_environment_id' => $this->foreignEnvironment->id, 'created_by' => $this->foreignAuthor->id, 'title' => 'Private assessment', 'status' => 'published', 'published_at' => now()]);
    $this->submission = AssignmentSubmission::query()->create(['assignment_id' => $this->assignment->id, 'learning_environment_id' => $this->foreignEnvironment->id, 'student_id' => $this->foreignAuthor->id, 'attempt' => 1, 'content' => 'Private response', 'submitted_at' => now()]);
    $this->suggestion = AiSuggestion::query()->create(['learning_environment_id' => $this->foreignEnvironment->id, 'requested_by' => $this->foreignAuthor->id, 'kind' => 'assignment', 'prompt' => 'Private prompt', 'title' => 'Private draft', 'content' => 'Private content']);
});

test('non-platform roles cannot access foreign-school resources through direct URLs', function (string $email): void {
    $user = User::query()->where('email', $email)->firstOrFail();

    $this->actingAs($user)->get(route('schools.members', $this->foreignSchool))->assertForbidden();
    $this->actingAs($user)->get(route('schools.academic', $this->foreignSchool))->assertForbidden();
    $this->actingAs($user)->get(route('learning-environments.members', $this->foreignEnvironment))->assertForbidden();
    $this->actingAs($user)->get(route('learning-environments.materials', $this->foreignEnvironment))->assertForbidden();
    $this->actingAs($user)->get(route('learning-environments.assignments', $this->foreignEnvironment))->assertForbidden();
    $this->actingAs($user)->get(route('learning-environments.assessments', $this->foreignEnvironment))->assertForbidden();
    $this->actingAs($user)->get(route('learning-environments.ai-assistance', $this->foreignEnvironment))->assertForbidden();
    $this->actingAs($user)->get(route('assignments.submissions', $this->assignment))->assertForbidden();
    $this->actingAs($user)->get(route('assessments.results', $this->assessment))->assertForbidden();

    expect($user->can('view', $this->submission))->toBeFalse()
        ->and($user->can('view', $this->suggestion))->toBeFalse();
})->with([
    'school administrator' => 'school.admin@skwelahub.test',
    'teacher' => 'teacher@skwelahub.test',
    'student' => 'student@skwelahub.test',
    'parent guardian' => 'parent@skwelahub.test',
]);

test('school administrators can supervise their academic structure without gaining classroom-content access', function (): void {
    $schoolAdmin = User::query()->where('email', 'school.admin@skwelahub.test')->firstOrFail();
    $demoEnvironment = LearningEnvironment::query()->where('name', 'Grade 7')->where('section', 'A')->firstOrFail();

    expect($schoolAdmin->can('manageMemberships', $demoEnvironment->school))->toBeTrue()
        ->and($schoolAdmin->can('view', $demoEnvironment))->toBeTrue();

    $this->actingAs($schoolAdmin)
        ->get(route('learning-environments.materials', $demoEnvironment))
        ->assertForbidden();
});

test('the super administrator can supervise school structure without managing school users or classroom content', function (): void {
    $superAdmin = User::query()->where('email', 'super.admin@skwelahub.test')->firstOrFail();

    $this->actingAs($superAdmin)->get(route('schools.members', $this->foreignSchool))->assertForbidden();
    $this->actingAs($superAdmin)->get(route('schools.academic', $this->foreignSchool))->assertSuccessful();
    $this->actingAs($superAdmin)->get(route('learning-environments.materials', $this->foreignEnvironment))->assertForbidden();
});

test('each seeded school has an independent classroom learning path', function (): void {
    foreach ([
        ['Grade 7', 'A', 'teacher@skwelahub.test', 'student@skwelahub.test'],
        ['Grade 8', 'B', 'north.teacher@skwelahub.test', 'north.student@skwelahub.test'],
    ] as [$name, $section, $teacherEmail, $studentEmail]) {
        $learningEnvironment = LearningEnvironment::query()->where('name', $name)->where('section', $section)->firstOrFail();
        $teacher = User::query()->where('email', $teacherEmail)->firstOrFail();
        $student = User::query()->where('email', $studentEmail)->firstOrFail();

        expect($learningEnvironment->materials()->exists())->toBeTrue()
            ->and($learningEnvironment->assignments()->where('status', 'published')->exists())->toBeTrue()
            ->and($learningEnvironment->assessments()->where('status', 'published')->exists())->toBeTrue()
            ->and($teacher->can('view', $learningEnvironment))->toBeTrue()
            ->and($student->can('view', $learningEnvironment))->toBeTrue();
    }

    $northTeacher = User::query()->where('email', 'north.teacher@skwelahub.test')->firstOrFail();
    $demoEnvironment = LearningEnvironment::query()->where('name', 'Grade 7')->where('section', 'A')->firstOrFail();

    $this->actingAs($northTeacher)
        ->get(route('learning-environments.assignments', $demoEnvironment))
        ->assertForbidden();
});
