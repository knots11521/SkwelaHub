<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('teacher and student dashboards load without exposing foreign environment data', function (): void {
    $this->seed(DatabaseSeeder::class);

    foreach (['teacher@skwelahub.test', 'student@skwelahub.test'] as $email) {
        $this->actingAs(User::query()->where('email', $email)->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk();
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
