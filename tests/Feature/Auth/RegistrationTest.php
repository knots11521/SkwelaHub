<?php

use App\Models\Invite;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('school admin can register and create a school', function () {
    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::SchoolAdmin->value,
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

    expect($user->hasRole(SchoolRole::SchoolAdmin->value))->toBeTrue()
        ->and($user->school_id)->toBe(School::query()->where('slug', 'john-doe-academy')->value('id'));
});

test('school admin registration screen can be rendered', function () {
    $response = $this->get(route('register.school-admin'));

    $response->assertOk();
});

test('teacher can register without role selection', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Teacher',
        'email' => 'teacher@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('join-school', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'teacher@example.com')->firstOrFail();

    expect($user->getRoleNames())->toBeEmpty()
        ->and($user->school_id)->toBeNull();
});

test('student can register with a valid invite code', function () {
    $school = School::factory()->create();
    $invite = Invite::factory()->forRole(SchoolRole::Student)->forSchool($school)->create(['code' => 'VALID123']);

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Student->value,
        'invite_code' => 'VALID123',
        'name' => 'New Student',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'student@example.com')->firstOrFail();

    expect($user->hasRole(SchoolRole::Student->value))->toBeTrue()
        ->and($user->school_id)->toBe($school->id);
});

test('parent can register with a valid invite code and link to student', function () {
    $school = School::factory()->create();
    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $invite = Invite::factory()->forRole(SchoolRole::ParentGuardian)->forSchool($school)->forStudent($student)->create(['code' => 'PARENT456']);

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::ParentGuardian->value,
        'invite_code' => 'PARENT456',
        'name' => 'New Parent',
        'email' => 'parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'parent@example.com')->firstOrFail();

    expect($user->hasRole(SchoolRole::ParentGuardian->value))->toBeTrue()
        ->and($user->school_id)->toBe($school->id)
        ->and($user->students->pluck('id')->toArray())->toEqual([$student->id]);
});

test('invite code cannot be used after expiration', function () {
    $school = School::factory()->create();
    $invite = Invite::factory()->forRole(SchoolRole::Student)->forSchool($school)->expired()->create(['code' => 'EXPIRED']);

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Student->value,
        'invite_code' => 'EXPIRED',
        'name' => 'New Student',
        'email' => 'student2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('invite_code');
});

test('invite code role mismatch is rejected', function () {
    $school = School::factory()->create();
    $invite = Invite::factory()->forRole(SchoolRole::Student)->forSchool($school)->create(['code' => 'ROLEMISMATCH']);

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Teacher->value,
        'invite_code' => 'ROLEMISMATCH',
        'name' => 'Role Mismatch',
        'email' => 'mismatch@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('role');
});

test('teacher can register with a valid invite code', function () {
    $school = School::factory()->create();
    $invite = Invite::factory()->forRole(SchoolRole::Teacher)->forSchool($school)->create(['code' => 'TEACHER789']);

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Teacher->value,
        'invite_code' => 'TEACHER789',
        'name' => 'Invited Teacher',
        'email' => 'invited.teacher@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'invited.teacher@example.com')->firstOrFail();

    expect($user->hasRole(SchoolRole::Teacher->value))->toBeTrue()
        ->and($user->school_id)->toBe($school->id);
});
