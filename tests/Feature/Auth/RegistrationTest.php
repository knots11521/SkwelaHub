<?php

use App\Models\School;
use App\Models\User;
use App\SchoolRole;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'school_name' => 'John Doe Academy',
        'school_address' => '123 Learning Lane',
        'school_region' => 'Central',
        'school_slug' => 'john-doe-academy',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect($user->role)->toBe(SchoolRole::SchoolAdmin)
        ->and($user->school_id)->toBe(School::query()->where('slug', 'john-doe-academy')->value('id'))
        ->and($user->hasRole(SchoolRole::SchoolAdmin->value))->toBeTrue();
});
