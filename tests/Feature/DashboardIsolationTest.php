<?php

use App\Models\Assignment;
use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('teacher and student dashboards only expose their assigned learning environment data', function (): void {
    $this->seed(DatabaseSeeder::class);
    $northCampus = School::query()->where('slug', 'skwelahub-north-campus')->firstOrFail();
    $subject = Subject::query()->create(['school_id' => $northCampus->id, 'name' => 'North Campus Maths', 'code' => 'NORTH-MATH']);
    $foreignEnvironment = LearningEnvironment::query()->create(['school_id' => $northCampus->id, 'subject_id' => $subject->id, 'name' => 'North Campus Class', 'section' => 'B']);
    Assignment::query()->create([
        'learning_environment_id' => $foreignEnvironment->id,
        'created_by' => User::query()->where('email', 'school.admin@skwelahub.test')->firstOrFail()->id,
        'title' => 'North Campus Private Assignment',
        'status' => 'published',
        'published_at' => now(),
    ]);

    foreach (['teacher@skwelahub.test', 'student@skwelahub.test'] as $email) {
        $this->actingAs(User::query()->where('email', $email)->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Grade 7')
            ->assertDontSee('North Campus Class')
            ->assertDontSee('North Campus Private Assignment');
    }
});

test('the guardian dashboard remains limited without a student relationship', function (): void {
    $this->seed(DatabaseSeeder::class);

    $this->actingAs(User::query()->where('email', 'parent@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Guardian access')
        ->assertSee('does not expose any student data')
        ->assertDontSee('Teacher User')
        ->assertDontSee('Student User');
});
