<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['skwelahub-demonstration-school', 'SkwelaHub Demonstration School'],
            ['skwelahub-north-campus', 'SkwelaHub North Campus'],
        ] as [$slug, $name]) {
            School::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => 'A local demonstration school for V1 development.', 'is_active' => true],
            );
        }
    }
}
