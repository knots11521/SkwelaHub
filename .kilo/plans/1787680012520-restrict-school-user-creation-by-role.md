# Plan: Restrict school user creation by role

## Goal
- Teachers can add **students** and **parents/guardians** to their school.
- School admins can add **only teachers** to their school.
- Teachers can access the school members management page; school admins keep their existing access.

## Current state
- `app/Policies/SchoolPolicy.php` — `manageMemberships` is limited to `SchoolAdmin`.
- `app/Livewire/Schools/Members.php` — allows creating `Teacher`, `Student`, and `ParentGuardian`; gated by `manageMemberships`.
- `resources/views/livewire/schools/members.blade.php` — shows all three roles in a dropdown.
- `resources/views/layouts/app/sidebar.blade.php` — "School users" link is visible only to `SchoolAdmin`.
- Tests cover school-admin creation and cross-school forbidden access.

## Proposed changes

### 1. Add `manageSchoolUsers` ability to `SchoolPolicy`
- New method `manageSchoolUsers(User $user, School $school): bool`
- Returns `true` when the user has an approved `SchoolAdmin` or `Teacher` role for the school.
- Keep `manageMemberships` unchanged to avoid side effects in `LearningEnvironmentMembershipPolicy`.

### 2. Update `Schools\Members` Livewire component
- Replace `manageMemberships` authorization with `manageSchoolUsers` in `mount()` and `createMember()`.
- Add a computed `availableRoles()` property:
  - `SchoolAdmin` → `[Teacher]`
  - `Teacher` → `[Student, ParentGuardian]`
- Update `memberRole` default in `mount()` to the first available role.
- Update validation to allow only `availableRoles()`.

### 3. Update `members.blade.php`
- Replace the hard-coded role `<flux:select.option>` list with a loop over `$availableRoles`.

### 4. Update sidebar navigation
- Show the "School users" link to teachers as well as school admins.

### 5. Tests
- Update `tests/Feature/MembershipApprovalWorkflowTest.php`:
  - Keep existing school-admin → teacher creation test.
  - Add teacher → student creation test.
  - Add teacher → parent/guardian creation test.
  - Add teacher cannot create teacher test.
  - Add school admin cannot create student test.
  - Add school admin cannot create parent/guardian test.
- Keep `AuthorizationHardeningTest` expectations intact (foreign-school access remains forbidden for all roles including teacher).

## Validation
- Run `php artisan test --compact` for:
  - `tests/Feature/MembershipApprovalWorkflowTest.php`
  - `tests/Feature/SchoolManagementTest.php`
  - `tests/Feature/AuthorizationHardeningTest.php`
  - `tests/Feature/SchoolMembershipTest.php`
- Run `vendor/bin/pint --format agent` after PHP edits.
