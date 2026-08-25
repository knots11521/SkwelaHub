<?php

namespace App\Actions\Performance;

use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\PerformanceRecord;

class RecordPerformance
{
    public function handle(AssignmentSubmission|AssessmentAttempt $source): PerformanceRecord
    {
        return PerformanceRecord::query()->updateOrCreate(
            ['source_type' => $source->getMorphClass(), 'source_id' => $source->id],
            [
                'student_id' => $source->student_id,
                'learning_environment_id' => $source->learning_environment_id,
                'score' => $source->score,
                'recorded_at' => $source->evaluated_at ?? $source->submitted_at ?? now(),
            ],
        );
    }
}
