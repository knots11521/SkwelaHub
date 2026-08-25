<?php

namespace App\Livewire\LearningEnvironments;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\SchoolMembership;
use App\SchoolRole;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageMembers extends Component
{
    public LearningEnvironment $learningEnvironment;

    public string $schoolMembershipId = '';

    public function mount(LearningEnvironment $learningEnvironment): void
    {
        $this->authorize('manageMembers', $learningEnvironment);
        $this->learningEnvironment = $learningEnvironment;
    }

    public function addMember(): void
    {
        $this->authorize('create', [LearningEnvironmentMembership::class, $this->learningEnvironment]);
        $data = $this->validate(['schoolMembershipId' => ['required', Rule::exists('school_memberships', 'id')]]);
        $membership = SchoolMembership::query()
            ->approved()
            ->whereBelongsTo($this->learningEnvironment->school)
            ->whereKey($data['schoolMembershipId'])
            ->where('requested_role', SchoolRole::Student->value)
            ->firstOrFail();
        LearningEnvironmentMembership::query()->firstOrCreate(['school_membership_id' => $membership->id, 'learning_environment_id' => $this->learningEnvironment->id]);
        $this->reset('schoolMembershipId');
        Flux::toast(variant: 'success', text: 'Class membership added.');
    }

    public function removeMember(int $id): void
    {
        $membership = $this->learningEnvironment->memberships()->findOrFail($id);
        $this->authorize('delete', $membership);
        $membership->delete();
        Flux::toast(text: 'Class membership removed.');
    }

    public function render()
    {
        return view('livewire.learning-environments.manage-members', [
            'available' => SchoolMembership::query()
                ->approved()
                ->whereBelongsTo($this->learningEnvironment->school)
                ->where('requested_role', SchoolRole::Student->value)
                ->with('user:id,name,email')
                ->get(),
            'memberships' => $this->learningEnvironment->memberships()
                ->with('schoolMembership.user:id,name,email')
                ->get(),
        ]);
    }
}
