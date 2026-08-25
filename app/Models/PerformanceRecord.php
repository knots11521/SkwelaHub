<?php

namespace App\Models;

use Database\Factories\PerformanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PerformanceRecord extends Model
{
    /** @use HasFactory<PerformanceRecordFactory> */
    use HasFactory;

    protected $fillable = ['student_id', 'learning_environment_id', 'source_type', 'source_id', 'score', 'recorded_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'recorded_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
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
