<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('each seeded role can load its real dashboard', function () {
    $this->seed(DatabaseSeeder::class);

    $this->actingAs(User::query()->where('email', 'super.admin@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Platform schools');

    $this->actingAs(User::query()->where('email', 'school.admin@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Academic structure');

    foreach (['teacher@skwelahub.test', 'student@skwelahub.test'] as $email) {
        $this->actingAs(User::query()->where('email', $email)->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Grade 7')
            ->assertSee('Materials');
    }

    $this->actingAs(User::query()->where('email', 'parent@skwelahub.test')->firstOrFail())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Guardian access');
});
