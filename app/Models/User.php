<?php

namespace App\Models;

use App\SchoolMembershipStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\SchoolRole;
use Database\Factories\UserFactory;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
#[Fillable(['school_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable {
        HasRoles::assignRole as protected traitAssignRole;
    }

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

    public function assignRole($role): static
    {
        if ($this->exists) {
            $this->roles()->detach();
            $this->unsetRelation('roles');
        }

        return $this->traitAssignRole($role);
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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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

    public function canAccessJoinSchool(): bool
    {
        if ($this->hasRole(RoleSeeder::SuperAdmin)
            || $this->hasRole(SchoolRole::SchoolAdmin->value)) {
            return false;
        }

        if ($this->hasRole(SchoolRole::Teacher->value)) {
            return ! $this->hasApprovedSchoolMembership();
        }

        return true;
    }

    public function hasApprovedSchoolMembership(): bool
    {
        if ($this->school_id !== null) {
            return true;
        }

        return $this->schoolMemberships()
            ->approved()
            ->exists();
    }

    public function hasPendingSchoolMembership(): bool
    {
        return $this->schoolMemberships()
            ->pending()
            ->exists();
    }

    public function hasApprovedMembershipInSchool(School $school): bool
    {
        if ($this->school_id === $school->id) {
            return true;
        }

        return $this->schoolMemberships()
            ->approved()
            ->whereBelongsTo($school)
            ->exists();
    }

    public function hasApprovedSchoolRole(School $school, SchoolRole $role): bool
    {
        if ($this->school_id === $school->id && $this->hasRole($role->value)) {
            return true;
        }

        return $this->hasRole($role->value) && $this->schoolMemberships()
            ->approved()
            ->whereBelongsTo($school)
            ->where('requested_role', $role->value)
            ->exists();
    }

    public function hasLearningEnvironmentRole(LearningEnvironment $environment, SchoolRole $role): bool
    {
        if (($this->school_id !== null && $this->school_id !== $environment->school_id)
            || ! $this->hasRole($role->value)) {
            return false;
        }

        return $this->schoolMemberships()
            ->approved()
            ->where('school_id', $environment->school_id)
            ->where('requested_role', $role->value)
            ->whereHas('learningEnvironmentMemberships', fn ($query) => $query->whereBelongsTo($environment))
            ->exists();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function isParentOf(int|User $student): bool
    {
        $studentId = $student instanceof User ? $student->id : $student;

        return $this->hasRole(SchoolRole::ParentGuardian->value)
            && $this->students()->whereKey($studentId)->exists();
    }

    public function isParentOfChildIn(LearningEnvironment $environment): bool
    {
        if (! $this->hasRole(SchoolRole::ParentGuardian->value)) {
            return false;
        }

        $childIds = $this->linkedStudentIds();

        if (empty($childIds)) {
            return false;
        }

        return $environment->memberships()
            ->whereHas('schoolMembership', function ($q) use ($childIds) {
                $q->where('status', SchoolMembershipStatus::Approved)
                    ->whereIn('user_id', $childIds);
            })
            ->exists();
    }

    public function linkedStudentIds(): array
    {
        return $this->students()->pluck('users.id')->all();
    }
}
