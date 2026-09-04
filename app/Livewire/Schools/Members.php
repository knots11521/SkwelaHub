<?php

namespace App\Livewire\Schools;

use App\Models\Invite;
use App\Models\LearningEnvironmentMembership;
use App\Models\School;
use App\Models\SchoolMembership;
use App\SchoolRole;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('School members')]
class Members extends Component
{
    public School $school;

    public bool $showInviteModal = false;

    public ?string $inviteExpiresAt = null;

    public ?Invite $lastGeneratedInvite = null;

    public function mount(School $school): void
    {
        $this->authorize('manageSchoolUsers', $school);
        $this->school = $school;
    }

    public function openInviteModal(): void
    {
        $this->reset(['inviteExpiresAt', 'lastGeneratedInvite']);
        $this->showInviteModal = true;
    }

    public function generateInvite(): void
    {
        $this->authorize('manageSchoolUsers', $this->school);

        $this->validate([
            'inviteExpiresAt' => ['nullable', 'date', 'after:today'],
        ]);

        $invite = Invite::query()->create([
            'school_id' => $this->school->id,
            'learning_environment_id' => null,
            'code' => strtoupper(Str::random(8)),
            'link_token' => Str::random(32),
            'role' => SchoolRole::Teacher,
            'expires_at' => $this->inviteExpiresAt ? Carbon::parse($this->inviteExpiresAt) : null,
            'created_by' => Auth::id(),
        ]);

        $this->lastGeneratedInvite = $invite;
        Flux::toast(variant: 'success', text: 'School invite generated.');
    }

    public function revokeInvite(int $inviteId): void
    {
        $this->authorize('manageSchoolUsers', $this->school);

        $invite = Invite::query()->where('school_id', $this->school->id)->findOrFail($inviteId);
        $invite->delete();

        Flux::toast(variant: 'success', text: 'Invite revoked.');
    }

    public function removeUserFromSchool(int $userId): void
    {
        $this->authorize('manageSchoolUsers', $this->school);

        if ($userId === Auth::id()) {
            Flux::toast(variant: 'error', text: 'You cannot remove yourself.');

            return;
        }

        $memberships = SchoolMembership::query()
            ->where('school_id', $this->school->id)
            ->where('user_id', $userId)
            ->get();

        foreach ($memberships as $membership) {
            LearningEnvironmentMembership::query()
                ->where('school_membership_id', $membership->id)
                ->delete();
            $membership->delete();
        }

        Flux::toast(variant: 'success', text: 'User removed from school.');
    }

    #[Computed]
    public function members(): LengthAwarePaginator
    {
        return $this->school->users()
            ->select(['id', 'school_id', 'name', 'email', 'created_at'])
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function invites()
    {
        return Invite::query()
            ->where('school_id', $this->school->id)
            ->whereNull('learning_environment_id')
            ->with('creator:id,name')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.schools.members');
    }
}
