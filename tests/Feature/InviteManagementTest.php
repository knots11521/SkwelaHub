<?php

use App\Livewire\JoinSchool;
use App\Livewire\LearningEnvironments\ManageMembers;
use App\Models\Invite;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\ParentStudent;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('school admin can generate a teacher classroom invite', function () {
    $school = School::factory()->create();
    $schoolAdmin = User::factory()->create(['school_id' => $school->id]);
    $schoolAdmin->assignRole(SchoolRole::SchoolAdmin->value);
    $schoolAdminMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $schoolAdmin->id,
        'requested_role' => SchoolRole::SchoolAdmin,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $schoolAdmin->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $schoolAdmin->id, 'name' => 'Grade 7']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $schoolAdminMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($schoolAdmin)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->call('generateClassroomInvite')
        ->assertHasNoErrors();

    $invite = Invite::query()->where('learning_environment_id', $environment->id)->first();
    expect($invite)->not->toBeNull()
        ->and($invite->role)->toBe(SchoolRole::Teacher)
        ->and($invite->code)->not->toBeEmpty()
        ->and($invite->link_token)->not->toBeEmpty()
        ->and($invite->expires_at)->toBeNull();
});

test('teacher can generate a student classroom invite', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Science', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 8']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->call('generateClassroomInvite')
        ->assertHasNoErrors();

    $invite = Invite::query()->where('learning_environment_id', $environment->id)->first();
    expect($invite->role)->toBe(SchoolRole::Student);
});

test('teacher can generate a parent classroom invite for a specific student', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'English', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 9']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->set('classroomInviteRole', SchoolRole::ParentGuardian->value)
        ->set('classroomInviteStudentId', $student->id)
        ->call('generateClassroomInvite')
        ->assertHasNoErrors();

    $invite = Invite::query()->where('learning_environment_id', $environment->id)->first();
    expect($invite->role)->toBe(SchoolRole::ParentGuardian)
        ->and($invite->student_id)->toBe($student->id);
});

test('unauthenticated user is redirected to login when accepting invite link', function () {
    $school = School::factory()->create();
    $admin = User::factory()->create(['school_id' => $school->id]);
    $admin->assignRole(SchoolRole::SchoolAdmin->value);
    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $admin->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $admin->id, 'name' => 'Grade 7']);

    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create();

    $this->get(route('invite.accept', $invite->link_token))
        ->assertRedirect(route('login', ['invite_token' => $invite->link_token]));
});

test('student can join a classroom via invite code and is auto-registered in school', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create();

    $newStudent = User::factory()->create(['school_id' => null]);

    Livewire::actingAs($newStudent)
        ->test(JoinSchool::class)
        ->set('inviteCode', $invite->code)
        ->call('joinByInvite')
        ->assertHasNoErrors();

    $newStudent->refresh();
    expect($newStudent->school_id)->toBe($school->id)
        ->and($newStudent->hasRole(SchoolRole::Student->value))->toBeTrue();

    $membership = SchoolMembership::query()->where('user_id', $newStudent->id)->where('school_id', $school->id)->first();
    expect($membership)->not->toBeNull()
        ->and($membership->status)->toBe(SchoolMembershipStatus::Approved);

    $classMembership = LearningEnvironmentMembership::query()->where('learning_environment_id', $environment->id)->first();
    expect($classMembership)->not->toBeNull();
});

test('user cannot join a classroom in a different school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $schoolB->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $subject = Subject::query()->create(['school_id' => $schoolB->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $schoolB->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);
    $invite = Invite::factory()->forSchool($schoolB)->forLearningEnvironment($environment)->create();

    $student = User::factory()->create(['school_id' => $schoolA->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $schoolA->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    Livewire::actingAs($student)
        ->test(JoinSchool::class)
        ->assertSuccessful()
        ->set('inviteCode', $invite->code)
        ->call('joinByInvite');

    expect($student->fresh()->school_id)->toBe($schoolA->id)
        ->and(SchoolMembership::query()->where('user_id', $student->id)->where('school_id', $schoolB->id)->exists())->toBeFalse();
});

test('parent invite links create a parent-student relation', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);
    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create([
        'role' => SchoolRole::ParentGuardian,
        'student_id' => $student->id,
    ]);

    $parent = User::factory()->create(['school_id' => null]);

    Livewire::actingAs($parent)
        ->test(JoinSchool::class)
        ->set('inviteCode', $invite->code)
        ->call('joinByInvite')
        ->assertHasNoErrors();

    expect(ParentStudent::query()->where('parent_id', $parent->id)->where('student_id', $student->id)->exists())->toBeTrue();
});

test('logged-in user can join via invite link and is redirected to dashboard', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create();

    $student = User::factory()->create(['school_id' => null]);

    $this->actingAs($student)
        ->get(route('invite.accept', $invite->link_token))
        ->assertRedirect(route('dashboard'));

    $student->refresh();
    expect($student->school_id)->toBe($school->id)
        ->and($student->hasRole(SchoolRole::Student->value))->toBeTrue();

    $membership = SchoolMembership::query()->where('user_id', $student->id)->where('school_id', $school->id)->first();
    expect($membership)->not->toBeNull()
        ->and($membership->status)->toBe(SchoolMembershipStatus::Approved);

    $classMembership = LearningEnvironmentMembership::query()->where('learning_environment_id', $environment->id)->first();
    expect($classMembership)->not->toBeNull();
});

test('teacher can generate invite with explicit role dropdown', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Science', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 8']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->set('classroomInviteRole', SchoolRole::Student->value)
        ->call('generateClassroomInvite')
        ->assertHasNoErrors();

    $invite = Invite::query()->where('learning_environment_id', $environment->id)->first();
    expect($invite)->not->toBeNull()
        ->and($invite->role)->toBe(SchoolRole::Student);
});

test('teacher can generate parent invite with explicit role dropdown and student selection', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'English', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 9']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->set('classroomInviteRole', SchoolRole::ParentGuardian->value)
        ->set('classroomInviteStudentId', $student->id)
        ->call('generateClassroomInvite')
        ->assertHasNoErrors();

    $invite = Invite::query()->where('learning_environment_id', $environment->id)->first();
    expect($invite)->not->toBeNull()
        ->and($invite->role)->toBe(SchoolRole::ParentGuardian)
        ->and($invite->student_id)->toBe($student->id);
});

test('parent invite requires student selection', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    $teacherMembership = SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);
    LearningEnvironmentMembership::query()->create([
        'school_membership_id' => $teacherMembership->id,
        'learning_environment_id' => $environment->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMembers::class, ['learningEnvironment' => $environment])
        ->call('openInviteModal')
        ->set('classroomInviteRole', SchoolRole::ParentGuardian->value)
        ->call('generateClassroomInvite')
        ->assertHasErrors('classroomInviteStudentId');
});

test('logged-in user can join via invite link when redirected from login with token query param', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create();

    $student = User::factory()->create(['school_id' => null]);

    $this->actingAs($student)
        ->get(route('invite.accept', ['link_token' => $invite->link_token, 'invite_token' => $invite->link_token]))
        ->assertRedirect(route('dashboard'));

    $student->refresh();
    expect($student->school_id)->toBe($school->id)
        ->and($student->hasRole(SchoolRole::Student->value))->toBeTrue();

    expect(SchoolMembership::query()->where('user_id', $student->id)->where('school_id', $school->id)->exists())->toBeTrue();
    expect(LearningEnvironmentMembership::query()->where('learning_environment_id', $environment->id)->exists())->toBeTrue();
});

test('new user can register via invite link token and is auto-joined to school and classroom', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $invite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create();

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Student->value,
        'invite_token' => $invite->link_token,
        'name' => 'Token Student',
        'email' => 'token-student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'token-student@example.com')->firstOrFail();

    expect($user->hasRole(SchoolRole::Student->value))->toBeTrue()
        ->and($user->school_id)->toBe($school->id);

    expect(SchoolMembership::query()->where('user_id', $user->id)->where('school_id', $school->id)->exists())->toBeTrue();
    expect(LearningEnvironmentMembership::query()->where('learning_environment_id', $environment->id)->exists())->toBeTrue();
});

test('parent can register via invite link token and links to specified student', function () {
    $school = School::factory()->create();
    $student = User::factory()->create(['school_id' => $school->id]);
    $student->assignRole(SchoolRole::Student->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $student->id,
        'requested_role' => SchoolRole::Student,
    ]);

    $invite = Invite::factory()->forRole(SchoolRole::ParentGuardian)->forSchool($school)->forStudent($student)->create();

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::ParentGuardian->value,
        'invite_token' => $invite->link_token,
        'name' => 'Token Parent',
        'email' => 'token-parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'token-parent@example.com')->firstOrFail();

    expect($user->hasRole(SchoolRole::ParentGuardian->value))->toBeTrue()
        ->and($user->school_id)->toBe($school->id)
        ->and($user->students->pluck('id')->toArray())->toEqual([$student->id]);

    expect(ParentStudent::query()->where('parent_id', $user->id)->where('student_id', $student->id)->exists())->toBeTrue();
});

test('expired invite link token is rejected during registration', function () {
    $school = School::factory()->create();
    $invite = Invite::factory()->forSchool($school)->expired()->create();

    $response = $this->post(route('register.store'), [
        'role' => SchoolRole::Student->value,
        'invite_token' => $invite->link_token,
        'name' => 'Expired Invite',
        'email' => 'expired-invite@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('invite_token');
});

test('teacher can revoke an invite and generate a new one without 404', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole(SchoolRole::Teacher->value);
    SchoolMembership::factory()->approved()->create([
        'school_id' => $school->id,
        'user_id' => $teacher->id,
        'requested_role' => SchoolRole::Teacher,
    ]);

    $subject = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math', 'created_by' => $teacher->id]);
    $environment = LearningEnvironment::query()->create(['school_id' => $school->id, 'subject_id' => $subject->id, 'created_by' => $teacher->id, 'name' => 'Grade 7']);

    $firstInvite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create([
        'created_by' => $teacher->id,
        'role' => SchoolRole::Student,
    ]);

    $firstInvite->delete();
    expect(Invite::query()->find($firstInvite->id))->toBeNull();

    $secondInvite = Invite::factory()->forSchool($school)->forLearningEnvironment($environment)->create([
        'created_by' => $teacher->id,
        'role' => SchoolRole::Student,
    ]);

    expect($secondInvite->id)->not->toBe($firstInvite->id)
        ->and($secondInvite->link_token)->not->toBe($firstInvite->link_token);

    $this->actingAs($teacher)
        ->get(route('invite.accept', $secondInvite->link_token))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});
