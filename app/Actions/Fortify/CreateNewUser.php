<?php

namespace App\Actions\Fortify;

use App\Actions\Schools\RegisterSchoolAdmin;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:255'],
            'school_region' => ['nullable', 'string', 'max:255'],
            'school_slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique(School::class, 'slug')],
        ])->validate();

        return (new RegisterSchoolAdmin)->handle($input);
    }
}
