<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use App\SchoolRole;
use Illuminate\Database\Seeder;

class SchoolMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['school.admin@skwelahub.test', SchoolRole::SchoolAdmin, 'skwelahub-demonstration-school'],
            ['teacher@skwelahub.test', SchoolRole::Teacher, 'skwelahub-demonstration-school'],
            ['student@skwelahub.test', SchoolRole::Student, 'skwelahub-demonstration-school'],
            ['parent@skwelahub.test', SchoolRole::ParentGuardian, 'skwelahub-demonstration-school'],
            ['north.teacher@skwelahub.test', SchoolRole::Teacher, 'skwelahub-north-campus'],
            ['north.student@skwelahub.test', SchoolRole::Student, 'skwelahub-north-campus'],
            ['north.admin@skwelahub.test', SchoolRole::SchoolAdmin, 'skwelahub-north-campus'],
        ] as [$email, $role, $schoolSlug]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $school = School::query()->where('slug', $schoolSlug)->firstOrFail();
            $user->update(['school_id' => $school->id]);

            SchoolMembership::query()->updateOrCreate(
                ['school_id' => $school->id, 'user_id' => $user->id],
                ['requested_role' => $role, 'status' => SchoolMembershipStatus::Approved, 'reviewed_at' => now()],
            );
        }
    }
}
