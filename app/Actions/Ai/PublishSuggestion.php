<?php

namespace App\Actions\Ai;

use App\Models\AiSuggestion;
use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\User;
use DomainException;

class PublishSuggestion
{
    public function handle(User $teacher, AiSuggestion $suggestion): Assignment|LearningMaterial|null
    {
        if ($suggestion->status !== 'approved') {
            throw new DomainException('Only an approved teacher suggestion can be published.');
        }

        $publishedContent = match ($suggestion->kind) {
            'assignment' => Assignment::query()->create([
                'learning_environment_id' => $suggestion->learning_environment_id,
                'created_by' => $teacher->id,
                'title' => $suggestion->title,
                'instructions' => $suggestion->content,
                'status' => 'draft',
            ]),
            'material' => LearningMaterial::query()->create([
                'learning_environment_id' => $suggestion->learning_environment_id,
                'created_by' => $teacher->id,
                'title' => $suggestion->title,
                'type' => 'text',
                'content' => $suggestion->content,
            ]),
            default => null,
        };

        $suggestion->update(['status' => 'published', 'published_at' => now()]);

        return $publishedContent;
    }
}
