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
    public function handle(User $teacher, LearningEnvironment $learningEnvironment, string $kind, string $prompt): AiSuggestion
    {
        $apiKey = config('services.openai.key');
        if (blank($apiKey)) {
            throw new RuntimeException('AI assistance is unavailable until OPENAI_API_KEY is configured.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model'),
                    'input' => "You are assisting a teacher. Create a concise {$kind} suggestion for {$learningEnvironment->name}. Teacher request: {$prompt}\n\nReturn plain text with a first line beginning 'Title:' and the remaining content suitable for teacher review. Do not claim the content is published or graded.",
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new RuntimeException('AI assistance could not be reached. Please try again.', previous: $exception);
        }

        $output = data_get($response->json(), 'output.0.content.0.text');
        if (! is_string($output) || blank($output)) {
            throw new RuntimeException('AI assistance returned no usable suggestion.');
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

    /** @return array{string, string} */
    private function splitOutput(string $output): array
    {
        $lines = preg_split('/\R/', trim($output), 2);
        $firstLine = $lines[0] ?? 'Teacher suggestion';
        $title = str($firstLine)->after('Title:')->trim()->limit(255)->toString();

        return [blank($title) ? 'Teacher suggestion' : $title, trim($lines[1] ?? $output)];
    }
}
