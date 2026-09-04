<?php

namespace App\Models;

use App\SchoolRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'learning_environment_id', 'code', 'link_token', 'role', 'student_id', 'expires_at', 'created_by'])]
class Invite extends Model
{
    /** @use HasFactory<InviteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'role' => SchoolRole::class,
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'link_token';
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningEnvironment(): BelongsTo
    {
        return $this->belongsTo(LearningEnvironment::class);
    }

    public function getLinkAttribute(): string
    {
        return route('invite.accept', $this->link_token);
    }

    #[Scope]
    protected function valid(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function isValid(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
