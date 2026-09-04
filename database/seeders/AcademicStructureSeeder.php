<?php

namespace Database\Seeders;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['skwelahub-demonstration-school', 'teacher@skwelahub.test', 'Mathematics 7', 'Grade 7', 'A'],
            ['skwelahub-north-campus', 'north.teacher@skwelahub.test', 'Science 8', 'Grade 8', 'B'],
        ] as [$schoolSlug, $teacherEmail, $subjectName, $environmentName, $section]) {
            $school = School::query()->where('slug', $schoolSlug)->firstOrFail();
            $teacher = User::query()->where('email', $teacherEmail)->firstOrFail();
            $subject = Subject::query()->firstOrCreate(
                ['school_id' => $school->id, 'name' => $subjectName],
                ['created_by' => $teacher->id, 'description' => 'A demonstration subject for interface testing.'],
            );

            $subject->update(['created_by' => $teacher->id]);

            $learningEnvironment = LearningEnvironment::query()->firstOrCreate(
                ['school_id' => $school->id, 'name' => $environmentName, 'section' => $section],
                [
                    'subject_id' => $subject->id,
                    'created_by' => $teacher->id,
                    'description' => 'A demonstration learning environment for interface testing.',
                ],
            );

            $learningEnvironment->update([
                'subject_id' => $subject->id,
                'created_by' => $teacher->id,
            ]);
        }
    }
}
