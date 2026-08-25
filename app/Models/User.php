<?php

namespace App\Models;

use App\SchoolRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * @return HasMany<SchoolMembership, $this>
     */
    public function schoolMemberships(): HasMany
    {
        return $this->hasMany(SchoolMembership::class);
    }

    /**
     * @return HasMany<PerformanceRecord, $this>
     */
    public function performanceRecords(): HasMany
    {
        return $this->hasMany(PerformanceRecord::class, 'student_id');
    }

    /**
     * @return HasMany<GamificationEvent, $this>
     */
    public function gamificationEvents(): HasMany
    {
        return $this->hasMany(GamificationEvent::class);
    }

    /**
     * @return HasMany<UserAchievement, $this>
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function hasApprovedSchoolMembership(School $school): bool
    {
        return $this->schoolMemberships()
            ->approved()
            ->whereBelongsTo($school)
            ->exists();
    }

    public function hasApprovedSchoolRole(School $school, SchoolRole $role): bool
    {
        return $this->hasRole($role->value)
            && $this->schoolMemberships()
                ->approved()
                ->whereBelongsTo($school)
                ->where('requested_role', $role->value)
                ->exists();
    }

    public function hasLearningEnvironmentRole(LearningEnvironment $environment, SchoolRole $role): bool
    {
        return $this->schoolMemberships()->approved()->whereBelongsTo($environment->school)->where('requested_role', $role->value)->whereHas('learningEnvironmentMemberships', fn ($query) => $query->whereBelongsTo($environment))->exists();
    }
}
