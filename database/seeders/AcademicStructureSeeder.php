<?php

namespace Database\Seeders;

use App\Models\LearningEnvironment;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['skwelahub-demonstration-school', 'MATH-7', 'Mathematics 7', 'Grade 7', 'A'],
            ['skwelahub-north-campus', 'SCI-8', 'Science 8', 'Grade 8', 'B'],
        ] as [$schoolSlug, $code, $subjectName, $environmentName, $section]) {
            $school = School::query()->where('slug', $schoolSlug)->firstOrFail();
            $subject = Subject::query()->firstOrCreate(
                ['school_id' => $school->id, 'code' => $code],
                ['name' => $subjectName, 'description' => 'A demonstration subject for interface testing.'],
            );

            LearningEnvironment::query()->firstOrCreate(
                ['school_id' => $school->id, 'name' => $environmentName, 'section' => $section],
                [
                    'subject_id' => $subject->id,
                    'description' => 'A demonstration learning environment for interface testing.',
                ],
            );
        }
    }
}
