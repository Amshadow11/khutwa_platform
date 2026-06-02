<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;

class GeminiProvider
{
    private string $model;

    public function __construct()
    {
        $this->model = config('ai.models.gemini', 'gemini-2.5-flash');
    }

    public function complete(string $prompt, array $options = []): array
    {
        $response = Http::timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . env('GEMINI_API_KEY'),
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'maxOutputTokens' => $options['max_tokens'] ?? 1500,
                    ],
                ]
            );

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Gemini API Error: ' . $response->body()
            );
        }

        $data = $response->json();

        return [
            'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'model' => $this->model,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ];
    }

    public function completeWithSystem(
        string $system,
        string $prompt,
        array $options = []
    ): array {
        return $this->complete(
            $system . "\n\n" . $prompt,
            $options
        );
    }

    public function getModel(): string
    {
        return $this->model;
    }
}