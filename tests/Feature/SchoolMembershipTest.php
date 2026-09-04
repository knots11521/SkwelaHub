<?php

use App\Models\School;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('a direct school user is not a member of another school', function (): void {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);

    expect($student->can('view', $school))->toBeTrue()
        ->and($student->can('view', $otherSchool))->toBeFalse();
});
