<?php

namespace App\Models;

use Database\Factories\LearningEnvironmentMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningEnvironmentMembership extends Model
{
    /** @use HasFactory<LearningEnvironmentMembershipFactory> */
    use HasFactory;

    protected $fillable = ['school_membership_id', 'learning_environment_id'];

    public function schoolMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class);
    }

    public function learningEnvironment(): BelongsTo
    {
        return $this->belongsTo(LearningEnvironment::class);
    }
}
