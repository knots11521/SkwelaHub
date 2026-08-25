<?php

namespace App\Actions\Ai;

use App\Models\AiSuggestion;
use App\Models\LearningEnvironment;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GenerateSuggestion
{
    public function handle(
        User $teacher,
        LearningEnvironment $learningEnvironment,
        string $kind,
        string $prompt
    ): AiSuggestion {
        $apiKey = config('services.openrouter.key');

        if (blank($apiKey)) {
            throw new RuntimeException(
                'AI assistance is unavailable until OPENROUTER_API_KEY is configured.'
            );
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.model', 'stealth/ox-alpha'),

                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "You are assisting a teacher.

Create a concise {$kind} suggestion for the learning environment:
{$learningEnvironment->name}

Teacher request:
{$prompt}

Return plain text with a first line beginning exactly with:
Title:

The remaining content should be suitable for teacher review.

Do not claim that the content has been published, assigned, submitted, or graded.",
                        ],
                    ],

                    'reasoning' => [
                        'enabled' => true,
                    ],

                    'temperature' => 0.7,

                    'max_tokens' => 1500,

                    'stream' => false,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new RuntimeException(
                'AI assistance could not be reached. Please try again.',
                previous: $exception
            );
        }

        $output = data_get(
            $response->json(),
            'choices.0.message.content'
        );

        if (! is_string($output) || blank($output)) {
            throw new RuntimeException(
                'AI assistance returned no usable suggestion.'
            );
        }

        [$title, $content] = $this->splitOutput($output);

        return AiSuggestion::query()->create([
            'learning_environment_id' => $learningEnvironment->id,
            'requested_by' => $teacher->id,
            'kind' => $kind,
            'prompt' => $prompt,
            'title' => $title,
            'content' => $content,
        ]);
    }

    /**
     * @return array{string, string}
     */
    private function splitOutput(string $output): array
    {
        $lines = preg_split('/\R/', trim($output), 2);

        $firstLine = $lines[0] ?? 'Teacher suggestion';

        $title = str($firstLine)
            ->after('Title:')
            ->trim()
            ->limit(255)
            ->toString();

        return [
            blank($title) ? 'Teacher suggestion' : $title,
            trim($lines[1] ?? $output),
        ];
    }
}
