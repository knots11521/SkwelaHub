<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentStudent>
 */
class ParentStudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => User::factory(),
            'student_id' => User::factory(),
        ];
    }
}
