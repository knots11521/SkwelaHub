<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RoleSeeder::class, AchievementSeeder::class]);

        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'super.admin@skwelahub.test',
                'role' => RoleSeeder::SuperAdmin,
            ],
            [
                'name' => 'School Administrator',
                'email' => 'school.admin@skwelahub.test',
                'role' => RoleSeeder::SchoolAdmin,
            ],
            [
                'name' => 'Teacher User',
                'email' => 'teacher@skwelahub.test',
                'role' => RoleSeeder::Teacher,
            ],
            [
                'name' => 'Student User',
                'email' => 'student@skwelahub.test',
                'role' => RoleSeeder::Student,
            ],
            [
                'name' => 'Parent Guardian',
                'email' => 'parent@skwelahub.test',
                'role' => RoleSeeder::ParentGuardian,
            ],
            [
                'name' => 'North Campus Teacher',
                'email' => 'north.teacher@skwelahub.test',
                'role' => RoleSeeder::Teacher,
            ],
            [
                'name' => 'North Campus Student',
                'email' => 'north.student@skwelahub.test',
                'role' => RoleSeeder::Student,
            ],
        ];

        foreach ($users as $attributes) {
            $user = User::query()->firstOrCreate(
                ['email' => $attributes['email']],
                [
                    'name' => $attributes['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$attributes['role']]);
        }

        foreach ([
            ['School Admin Applicant', 'school.admin.applicant@skwelahub.test'],
            ['Teacher Applicant', 'teacher.applicant@skwelahub.test'],
            ['Student Applicant', 'student.applicant@skwelahub.test'],
            ['Parent Applicant', 'parent.applicant@skwelahub.test'],
        ] as [$name, $email]) {
            User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
            );
        }

        $this->call([
            SchoolSeeder::class,
            SchoolMembershipSeeder::class,
            AcademicStructureSeeder::class,
            LearningEnvironmentMembershipSeeder::class,
            LearningMaterialSeeder::class,
            LearningActivitySeeder::class,
        ]);
    }
}
