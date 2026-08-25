<?php

namespace App\Models;

use Database\Factories\GamificationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GamificationEvent extends Model
{
    /** @use HasFactory<GamificationEventFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'learning_environment_id', 'source_type', 'source_id', 'points', 'reason', 'awarded_at'];

    protected function casts(): array
    {
        return ['awarded_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningEnvironment(): BelongsTo
    {
        return $this->belongsTo(LearningEnvironment::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
