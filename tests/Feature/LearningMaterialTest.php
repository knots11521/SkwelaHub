<?php

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\LearningMaterial;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));
test('only members of an environment can view its materials', function () {
    $school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'code' => 'M1']);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Seven']);
    $teacher = User::factory()->create();
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $teacher->id, 'requested_role' => SchoolRole::Teacher]);
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $teacherMembership->id, 'learning_environment_id' => $environment->id]);
    $material = LearningMaterial::query()->create(['learning_environment_id' => $environment->id, 'created_by' => $teacher->id, 'title' => 'Lesson', 'type' => 'text', 'content' => 'Hello']);
    $student = User::factory()->create();
    $student->assignRole(SchoolRole::Student->value);
    $studentMembership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $student->id, 'requested_role' => SchoolRole::Student]);
    expect($student->can('view', $material))->toBeFalse();
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $studentMembership->id, 'learning_environment_id' => $environment->id]);
    expect($student->can('view', $material))->toBeTrue();
});
