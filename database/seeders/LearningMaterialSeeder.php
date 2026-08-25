<?php

namespace Database\Seeders;

use App\Models\LearningEnvironment;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;

class LearningMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['Grade 7', 'A', 'teacher@skwelahub.test', 'Welcome to Mathematics 7'],
            ['Grade 8', 'B', 'north.teacher@skwelahub.test', 'Welcome to Science 8'],
        ] as [$name, $section, $email, $title]) {
            $learningEnvironment = LearningEnvironment::query()->where('name', $name)->where('section', $section)->firstOrFail();
            $teacher = User::query()->where('email', $email)->firstOrFail();

            LearningMaterial::query()->firstOrCreate(
                ['learning_environment_id' => $learningEnvironment->id, 'title' => $title],
                [
                    'created_by' => $teacher->id,
                    'type' => 'text',
                    'content' => 'Use this demonstration lesson to verify the teacher and student materials interface.',
                ],
            );
        }
    }
}
