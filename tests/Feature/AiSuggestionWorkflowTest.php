<?php

use App\Livewire\Ai\Assistant;
use App\Models\AiSuggestion;
use App\Models\Assignment;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    config()->set('services.openai.key', 'test-key');
    $school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Mathematics', 'code' => 'MATH-7']);
    $this->environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7', 'section' => 'A']);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->teacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $teacherMembership->id, 'learning_environment_id' => $this->environment->id]);

    $this->otherTeacher = User::factory()->create();
    $this->otherTeacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->otherTeacher->id, 'requested_role' => SchoolRole::Teacher]);

    $this->student = User::factory()->create();
    $this->student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $this->student->id, 'requested_role' => SchoolRole::Student]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $studentMembership->id, 'learning_environment_id' => $this->environment->id]);
});

test('teacher AI output remains private until it is reviewed, approved, and explicitly published', function (): void {
    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'output' => [['content' => [['text' => "Title: Integer reflection\nAsk learners to explain how they solved -4 + 7."]]]],
        ]),
    ]);

    $this->actingAs($this->teacher);
    Livewire::test(Assistant::class, ['learningEnvironment' => $this->environment])
        ->set('kind', 'assignment')
        ->set('prompt', 'Create a reflection task about integer addition.')
        ->call('generate');

    $suggestion = AiSuggestion::query()->firstOrFail();
    expect($suggestion->status)->toBe('draft')
        ->and(Assignment::query()->count())->toBe(0)
        ->and($this->student->can('view', $suggestion))->toBeFalse()
        ->and($this->otherTeacher->can('view', $suggestion))->toBeFalse();

    Livewire::test(Assistant::class, ['learningEnvironment' => $this->environment])
        ->call('publish', $suggestion->id)
        ->assertForbidden();

    Livewire::test(Assistant::class, ['learningEnvironment' => $this->environment])
        ->set("titles.{$suggestion->id}", 'Teacher-reviewed integer reflection')
        ->set("contents.{$suggestion->id}", 'Explain your solution to -4 + 7.')
        ->call('saveReview', $suggestion->id)
        ->call('approve', $suggestion->id)
        ->call('publish', $suggestion->id);

    expect($suggestion->refresh()->status)->toBe('published')
        ->and($suggestion->approved_by)->toBe($this->teacher->id)
        ->and(Assignment::query()->count())->toBe(1)
        ->and(Assignment::query()->firstOrFail()->status)->toBe('draft');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.openai.com/v1/responses'
        && $request->hasHeader('Authorization', 'Bearer test-key'));
});

test('only a teacher assigned to the environment can initiate AI assistance', function (): void {
    $this->actingAs($this->student);
    Livewire::test(Assistant::class, ['learningEnvironment' => $this->environment])
        ->assertForbidden();

    $this->actingAs($this->otherTeacher);
    Livewire::test(Assistant::class, ['learningEnvironment' => $this->environment])
        ->assertForbidden();
});
