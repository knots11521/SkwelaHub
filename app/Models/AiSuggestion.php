<?php

namespace App\Models;

use Database\Factories\AiSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSuggestion extends Model
{
    /** @use HasFactory<AiSuggestionFactory> */
    use HasFactory;

    protected $fillable = ['learning_environment_id', 'requested_by', 'kind', 'prompt', 'title', 'content', 'status', 'reviewed_at', 'approved_by', 'approved_at', 'published_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'approved_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function learningEnvironment(): BelongsTo
    {
        return $this->belongsTo(LearningEnvironment::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
