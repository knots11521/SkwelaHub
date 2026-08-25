<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Database\Seeder;

class LearningActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['Grade 7', 'A', 'teacher@skwelahub.test', 'Integers practice', 'Integers check-in', 'What is -3 + 8?', ['-11', '5', '11'], 1],
            ['Grade 8', 'B', 'north.teacher@skwelahub.test', 'Matter practice', 'Matter check-in', 'Which state of matter has a fixed shape?', ['Gas', 'Liquid', 'Solid'], 2],
        ] as [$name, $section, $email, $assignmentTitle, $assessmentTitle, $question, $options, $correctOption]) {
            $learningEnvironment = LearningEnvironment::query()->where('name', $name)->where('section', $section)->firstOrFail();
            $teacher = User::query()->where('email', $email)->firstOrFail();

            Assignment::query()->firstOrCreate(
                ['learning_environment_id' => $learningEnvironment->id, 'title' => $assignmentTitle],
                [
                    'created_by' => $teacher->id,
                    'instructions' => 'Complete this practice activity and explain your reasoning in your own words.',
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );

            $assessment = Assessment::query()->firstOrCreate(
                ['learning_environment_id' => $learningEnvironment->id, 'title' => $assessmentTitle],
                [
                    'created_by' => $teacher->id,
                    'instructions' => 'Choose the correct answer for this short check-in.',
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );

            $assessment->questions()->firstOrCreate(
                ['prompt' => $question],
                ['options' => $options, 'correct_option' => $correctOption],
            );
        }
    }
}
