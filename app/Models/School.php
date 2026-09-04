<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'address', 'region', 'is_active', 'status', 'created_by', 'reviewed'])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SchoolMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(SchoolMembership::class);
    }

    /**
     * @return HasMany<SchoolMembership, $this>
     */
    public function approvedMemberships(): HasMany
    {
        return $this->hasMany(SchoolMembership::class)->approved();
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Subject, $this> */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /** @return HasMany<LearningEnvironment, $this> */
    public function learningEnvironments(): HasMany
    {
        return $this->hasMany(LearningEnvironment::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    #[Scope]
    protected function reviewed(Builder $query): Builder
    {
        return $query->where('reviewed', true);
    }

    #[Scope]
    protected function unreviewed(Builder $query): Builder
    {
        return $query->where('reviewed', false);
    }
}
