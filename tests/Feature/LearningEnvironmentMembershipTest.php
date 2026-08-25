<?php

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('school membership alone does not grant learning environment access', function () {
    $school = School::factory()->create();
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'code' => 'M1']);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Grade 7']);
    $teacher = User::factory()->create();
    $teacher->assignRole(SchoolRole::Teacher->value);
    $membership = SchoolMembership::factory()->approved()->create(['school_id' => $school->id, 'user_id' => $teacher->id, 'requested_role' => SchoolRole::Teacher]);
    expect($teacher->can('view', $environment))->toBeFalse();
    LearningEnvironmentMembership::query()->create(['school_membership_id' => $membership->id, 'learning_environment_id' => $environment->id]);
    expect($teacher->can('view', $environment))->toBeTrue();
});
