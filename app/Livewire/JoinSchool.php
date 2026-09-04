<?php

namespace App\Livewire;

use App\Models\Invite;
use App\Models\LearningEnvironmentMembership;
use App\Models\ParentStudent;
use App\Models\SchoolMembership;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Seeders\RoleSeeder;
use DB;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Join School')]
class JoinSchool extends Component
{
    public string $inviteCode = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->canAccessJoinSchool()) {
            if ($user->hasRole(SchoolRole::Teacher->value)) {
                $this->redirect(route('dashboard'));

                return;
            }

            abort(403);
        }
    }

    public function render(): View
    {
        return view('livewire.join-school');
    }

    public function joinByInvite(): bool
    {
        $this->validate([
            'inviteCode' => ['required', 'string', 'max:100'],
        ]);

        $invite = Invite::query()->where('code', $this->inviteCode)->first();

        if (! $invite || ! $invite->isValid()) {
            Flux::toast(variant: 'error', text: 'This invite code is invalid or has expired.');

            return false;
        }

        try {
            $this->processInvite($invite);
        } catch (ValidationException $e) {
            Flux::toast(variant: 'error', text: $e->validator->errors()->first());

            return false;
        }

        $this->reset('inviteCode');

        Flux::toast(variant: 'success', text: 'You have successfully joined the classroom.');

        return true;
    }

    public function acceptInviteLink(string $linkToken): bool
    {
        $invite = Invite::query()->where('link_token', $linkToken)->first();

        if (! $invite || ! $invite->isValid()) {
            return false;
        }

        try {
            $this->processInvite($invite);
        } catch (ValidationException $e) {
            return false;
        }

        return true;
    }

    private function processInvite(Invite $invite): void
    {
        $user = Auth::user();

        DB::transaction(function () use ($invite, $user): void {
            $existingMembership = SchoolMembership::query()
                ->where('user_id', $user->id)
                ->where('school_id', $invite->school_id)
                ->where('status', SchoolMembershipStatus::Approved)
                ->first();

            if ($user->school_id !== null && $user->school_id !== $invite->school_id) {
                throw ValidationException::withMessages([
                    'inviteCode' => 'You already belong to a different school and cannot join this one.',
                ]);
            }

            if ($invite->learning_environment_id !== null) {
                $existingClassMembership = LearningEnvironmentMembership::query()
                    ->whereHas('schoolMembership', fn ($query) => $query->where('user_id', $user->id)->where('school_id', $invite->school_id))
                    ->where('learning_environment_id', $invite->learning_environment_id)
                    ->exists();

                if ($existingClassMembership) {
                    throw ValidationException::withMessages([
                        'inviteCode' => 'You are already enrolled in this classroom.',
                    ]);
                }
            }

            if ($user->school_id === null) {
                $user->forceFill(['school_id' => $invite->school_id])->save();
            }

            if (! $user->hasRole($invite->role->value)) {
                if ($user->hasAnyRole([
                    SchoolRole::SchoolAdmin->value,
                    SchoolRole::Teacher->value,
                    SchoolRole::Student->value,
                    SchoolRole::ParentGuardian->value,
                    RoleSeeder::SuperAdmin,
                ])) {
                    throw ValidationException::withMessages([
                        'inviteCode' => 'You already have an assigned role and cannot accept this invite.',
                    ]);
                }

                $user->assignRole($invite->role->value);
            }

            $schoolMembership = $existingMembership ?? SchoolMembership::query()->create([
                'school_id' => $invite->school_id,
                'user_id' => $user->id,
                'requested_role' => $invite->role,
                'status' => SchoolMembershipStatus::Approved,
                'reviewed_by' => $invite->created_by,
                'reviewed_at' => now(),
            ]);

            if ($invite->learning_environment_id !== null) {
                LearningEnvironmentMembership::query()->create([
                    'school_membership_id' => $schoolMembership->id,
                    'learning_environment_id' => $invite->learning_environment_id,
                ]);
            }

            if ($invite->role === SchoolRole::ParentGuardian && $invite->student_id !== null) {
                ParentStudent::query()->firstOrCreate([
                    'parent_id' => $user->id,
                    'student_id' => $invite->student_id,
                ]);
            }
        });
    }
}
