<?php

namespace App\Models;

use App\SchoolMembershipStatus;
use App\SchoolRole;
use Database\Factories\SchoolMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['school_id', 'user_id', 'requested_role', 'status', 'reviewed_by', 'reviewed_at'])]
class SchoolMembership extends Model
{
    /** @use HasFactory<SchoolMembershipFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    #[Scope]
    protected function approved(Builder $query): Builder
    {
        return $query->where('status', SchoolMembershipStatus::Approved);
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', SchoolMembershipStatus::Pending);
    }

    protected function casts(): array
    {
        return [
            'requested_role' => SchoolRole::class,
            'status' => SchoolMembershipStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function learningEnvironmentMemberships(): HasMany
    {
        return $this->hasMany(LearningEnvironmentMembership::class);
    }
}
