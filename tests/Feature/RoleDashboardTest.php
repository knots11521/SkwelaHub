<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('each seeded role can load its real dashboard', function () {
    $this->seed(DatabaseSeeder::class);

    $this->actingAs(User::query()->where('email', 'super.admin@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Platform Schools');

    $this->actingAs(User::query()->where('email', 'school.admin@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Academic Structure');

    $this->actingAs(User::query()->where('email', 'teacher@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Teaching Workspace')
        ->assertSee('Assignments');

    $this->actingAs(User::query()->where('email', 'student@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Learning Workspace')
        ->assertSee('Assignments');

    $this->actingAs(User::query()->where('email', 'parent@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Guardian access');
});
