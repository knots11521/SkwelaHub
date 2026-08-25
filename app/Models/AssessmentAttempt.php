<?php

namespace App\Models;

use Database\Factories\AssessmentAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    /** @use HasFactory<AssessmentAttemptFactory> */
    use HasFactory;

    protected $fillable = ['assessment_id', 'learning_environment_id', 'student_id', 'attempt', 'status', 'score', 'submitted_at', 'result_available_at', 'feedback', 'evaluation_status', 'evaluated_by', 'evaluated_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'submitted_at' => 'datetime', 'result_available_at' => 'datetime', 'evaluated_at' => 'datetime'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function learningEnvironment(): BelongsTo
    {
        return $this->belongsTo(LearningEnvironment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
