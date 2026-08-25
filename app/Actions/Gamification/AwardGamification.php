<?php

namespace App\Actions\Gamification;

use App\Models\Achievement;
use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\GamificationEvent;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

class AwardGamification
{
    public function handle(AssignmentSubmission|AssessmentAttempt $source, int $points, string $reason): GamificationEvent
    {
        return DB::transaction(function () use ($source, $points, $reason): GamificationEvent {
            $event = GamificationEvent::query()->firstOrCreate(
                [
                    'user_id' => $source->student_id,
                    'source_type' => $source->getMorphClass(),
                    'source_id' => $source->id,
                ],
                [
                    'learning_environment_id' => $source->learning_environment_id,
                    'points' => $points,
                    'reason' => $reason,
                    'awarded_at' => now(),
                ],
            );

            if ($event->wasRecentlyCreated) {
                $achievement = Achievement::query()->firstOrCreate(
                    ['code' => 'first-learning-activity'],
                    ['name' => 'First Step', 'description' => 'Completed a first learning activity.'],
                );

                if (GamificationEvent::query()->where('user_id', $source->student_id)->count() === 1) {
                    UserAchievement::query()->firstOrCreate(
                        ['user_id' => $source->student_id, 'achievement_id' => $achievement->id],
                        ['awarded_at' => now()],
                    );
                }
            }

            return $event;
        });
    }
}
