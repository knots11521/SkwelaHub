<?php

namespace App\Actions\Schools;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;

class CreateSchool
{
    /**
     * @param  array{name: string}  $attributes
     */
    public function handle(User $creator, array $attributes): School
    {
        return School::query()->create([
            'name' => $attributes['name'],
            'slug' => Str::slug($attributes['name']).'-'.Str::lower(Str::random(6)),
            'created_by' => $creator->id,
        ]);
    }
}
