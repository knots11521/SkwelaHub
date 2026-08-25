<?php

namespace Database\Seeders;

use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\SchoolMembership;
use App\Models\User;
use App\SchoolMembershipStatus;
use Illuminate\Database\Seeder;

class LearningEnvironmentMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['Grade 7', 'A', ['teacher@skwelahub.test', 'student@skwelahub.test']],
            ['Grade 8', 'B', ['north.teacher@skwelahub.test', 'north.student@skwelahub.test']],
        ] as [$name, $section, $emails]) {
            $learningEnvironment = LearningEnvironment::query()
                ->where('name', $name)
                ->where('section', $section)
                ->firstOrFail();

            foreach ($emails as $email) {
                $user = User::query()->where('email', $email)->firstOrFail();
                $schoolMembership = SchoolMembership::query()
                    ->whereBelongsTo($user)
                    ->whereBelongsTo($learningEnvironment->school)
                    ->where('status', SchoolMembershipStatus::Approved)
                    ->firstOrFail();

                LearningEnvironmentMembership::query()->firstOrCreate([
                    'school_membership_id' => $schoolMembership->id,
                    'learning_environment_id' => $learningEnvironment->id,
                ]);
            }
        }
    }
}
