<?php

use App\Livewire\Academic\Manage as AcademicManage;
use App\Livewire\Assessments\GlobalIndex as GlobalAssessmentsIndex;
use App\Livewire\Assessments\Results as AssessmentResults;
use App\Livewire\Assignments\GlobalIndex as GlobalAssignmentsIndex;
use App\Livewire\LearningMaterials\GlobalIndex as GlobalMaterialsIndex;
use App\Livewire\Performance\Index as PerformanceIndex;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\LearningMaterial;
use App\Models\ParentStudent;
use App\Models\PerformanceRecord;
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
    $this->otherSchool = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $this->school->id, 'name' => 'Math']);
    $this->environment = LearningEnvironment::query()->create([
        'school_id' => $this->school->id,
        'subject_id' => $subject->id,
        'name' => 'Grade 7',
        'section' => 'A',
    ]);

    $this->teacher = User::factory()->create(['school_id' => $this->school->id]);
    $this->teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $this->teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $this->environment->id,
    ]);

    $this->student = User::factory()->create(['school_id' => $this->school->id]);
    $this->student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $this->student->id,
        'requested_role' => SchoolRole::Student,
    ]);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $studentMembership->id,
        'learning_environment_id' => $this->environment->id,
    ]);

    $this->schoolAdmin = User::factory()->create(['school_id' => $this->school->id]);
    $this->schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $this->schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $this->parent = User::factory()->create(['school_id' => $this->school->id]);
    $this->parent->assignRole(SchoolRole::ParentGuardian->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $this->parent->id,
        'requested_role' => SchoolRole::ParentGuardian,
    ]);
    ParentStudent::query()->create([
        'parent_id' => $this->parent->id,
        'student_id' => $this->student->id,
    ]);
});

/*
|--------------------------------------------------------------------------
| T1 — /membership-requests removed
|--------------------------------------------------------------------------
*/

test('membership-requests route does not exist', function (): void {
    $this->actingAs($this->schoolAdmin)
        ->get('/membership-requests')
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| T2 — Academic\Manage redirect for teachers
|--------------------------------------------------------------------------
*/

test('a teacher visiting the academic structure page is redirected', function (): void {
    Livewire::actingAs($this->teacher)
        ->test(AcademicManage::class, ['school' => $this->school])
        ->assertRedirect(route('learning-environments.index'));
});

test('a student visiting the academic structure page is allowed by policy but sees read-only data', function (): void {
    Livewire::actingAs($this->student)
        ->test(AcademicManage::class, ['school' => $this->school])
        ->assertSuccessful();
});

/*
|--------------------------------------------------------------------------
| T3 — Assessments\Results empty state for students without attempts
|--------------------------------------------------------------------------
*/

test('a parent with a child who has attempted does not 500 on the results page', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Quiz',
        'status' => 'published',
        'published_at' => now(),
    ]);
    AssessmentAttempt::query()->create([
        'assessment_id' => $assessment->id,
        'learning_environment_id' => $this->environment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'score' => 80,
        'submitted_at' => now(),
        'result_available_at' => now(),
    ]);

    Livewire::actingAs($this->parent)
        ->test(AssessmentResults::class, ['assessment' => $assessment])
        ->assertSuccessful();
});

/*
|--------------------------------------------------------------------------
| T4 — Global index pages gated to Teacher/Student/Parent
|--------------------------------------------------------------------------
*/

test('school admin cannot access the global assessments hub', function (): void {
    Livewire::actingAs($this->schoolAdmin)
        ->test(GlobalAssessmentsIndex::class)
        ->assertForbidden();
});

test('school admin cannot access the global assignments hub', function (): void {
    Livewire::actingAs($this->schoolAdmin)
        ->test(GlobalAssignmentsIndex::class)
        ->assertForbidden();
});

test('school admin cannot access the global materials hub', function (): void {
    Livewire::actingAs($this->schoolAdmin)
        ->test(GlobalMaterialsIndex::class)
        ->assertForbidden();
});

test('parent with linked child can access the global assessments hub', function (): void {
    Livewire::actingAs($this->parent)
        ->test(GlobalAssessmentsIndex::class)
        ->assertSuccessful();
});

test('parent with no linked children is forbidden from the performance hub', function (): void {
    $orphan = User::factory()->create();
    $orphan->assignRole(SchoolRole::ParentGuardian->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->school->id,
        'user_id' => $orphan->id,
        'requested_role' => SchoolRole::ParentGuardian,
    ]);

    Livewire::actingAs($orphan)
        ->test(PerformanceIndex::class)
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| T6 — canAccessJoinSchool tightened
|--------------------------------------------------------------------------
*/

test('an approved student can still access join-school to join additional classrooms', function (): void {
    $this->actingAs($this->student)
        ->get(route('join-school'))
        ->assertOk();
});

test('an approved parent can still access join-school to join additional classrooms', function (): void {
    $this->actingAs($this->parent)
        ->get(route('join-school'))
        ->assertOk();
});

test('an unapproved student can still reach join-school', function (): void {
    $unapproved = User::factory()->create();
    $unapproved->assignRole(SchoolRole::Student->value);

    $this->actingAs($unapproved)
        ->get(route('join-school'))
        ->assertSuccessful();
});

test('an approved teacher is redirected away from join-school', function (): void {
    $this->actingAs($this->teacher)
        ->get(route('join-school'))
        ->assertRedirect(route('dashboard'));
});

/*
|--------------------------------------------------------------------------
| T6b — Learning environments index access control
|--------------------------------------------------------------------------
*/

test('super admin cannot access learning environments index', function (): void {
    $superAdmin = User::factory()->create(['school_id' => null]);
    $superAdmin->assignRole(RoleSeeder::SuperAdmin);

    $this->actingAs($superAdmin)
        ->get(route('learning-environments.index'))
        ->assertForbidden();
});

test('school admin cannot access learning environments index', function (): void {
    $this->actingAs($this->schoolAdmin)
        ->get(route('learning-environments.index'))
        ->assertForbidden();
});

test('student with approved membership can access learning environments index', function (): void {
    $this->actingAs($this->student)
        ->get(route('learning-environments.index'))
        ->assertSuccessful();
});

test('parent with approved membership can access learning environments index', function (): void {
    $this->actingAs($this->parent)
        ->get(route('learning-environments.index'))
        ->assertSuccessful();
});

/*
|--------------------------------------------------------------------------
| T7 — same-school policy enforcement
|--------------------------------------------------------------------------
*/

test('a teacher from a different school cannot create an assessment in this env', function (): void {
    $foreignTeacher = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $foreignTeacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $this->otherSchool->id,
        'user_id' => $foreignTeacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    expect($foreignTeacher->can('create', [Assessment::class, $this->environment]))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| T8 — parent read access
|--------------------------------------------------------------------------
*/

test('parent can see their child assessment attempt via viewResults policy', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Quiz',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $attempt = AssessmentAttempt::query()->create([
        'assessment_id' => $assessment->id,
        'learning_environment_id' => $this->environment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'score' => 80,
        'submitted_at' => now(),
        'result_available_at' => now(),
    ]);

    expect($this->parent->can('viewResults', $assessment))->toBeTrue()
        ->and($this->parent->can('view', $attempt))->toBeTrue();
});

test('parent cannot see another parents child assessment result', function (): void {
    $otherStudent = User::factory()->create(['school_id' => $this->school->id]);
    $otherStudent->assignRole(SchoolRole::Student->value);
    $otherParent = User::factory()->create(['school_id' => $this->school->id]);
    $otherParent->assignRole(SchoolRole::ParentGuardian->value);
    ParentStudent::query()->create([
        'parent_id' => $otherParent->id,
        'student_id' => $otherStudent->id,
    ]);

    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Quiz',
        'status' => 'published',
        'published_at' => now(),
    ]);
    AssessmentAttempt::query()->create([
        'assessment_id' => $assessment->id,
        'learning_environment_id' => $this->environment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'score' => 80,
        'submitted_at' => now(),
        'result_available_at' => now(),
    ]);

    expect($otherParent->can('viewResults', $assessment))->toBeFalse();
});

test('parent cannot evaluate an assessment attempt', function (): void {
    $assessment = Assessment::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Quiz',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $attempt = AssessmentAttempt::query()->create([
        'assessment_id' => $assessment->id,
        'learning_environment_id' => $this->environment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'score' => 80,
        'submitted_at' => now(),
        'result_available_at' => now(),
    ]);

    expect($this->parent->can('evaluate', $attempt))->toBeFalse();
});

test('parent performance index scopes to linked children', function (): void {
    PerformanceRecord::query()->create([
        'student_id' => $this->student->id,
        'learning_environment_id' => $this->environment->id,
        'source_type' => AssessmentAttempt::class,
        'source_id' => 1,
        'score' => 95,
        'recorded_at' => now(),
    ]);

    $otherStudent = User::factory()->create(['school_id' => $this->school->id]);
    $otherStudent->assignRole(SchoolRole::Student->value);
    PerformanceRecord::query()->create([
        'student_id' => $otherStudent->id,
        'learning_environment_id' => $this->environment->id,
        'source_type' => AssessmentAttempt::class,
        'source_id' => 2,
        'score' => 50,
        'recorded_at' => now(),
    ]);

    Livewire::actingAs($this->parent)
        ->test(PerformanceIndex::class)
        ->assertSuccessful()
        ->assertViewHas('performanceRecords', function ($records) {
            return $records->count() === 1 && $records->first()->student_id === $this->student->id;
        });
});

test('parent cannot delete a learning material', function (): void {
    $material = LearningMaterial::query()->create([
        'learning_environment_id' => $this->environment->id,
        'created_by' => $this->teacher->id,
        'title' => 'Notes',
        'type' => 'text',
        'content' => 'Hello',
    ]);

    expect($this->parent->can('delete', $material))->toBeFalse();
});
