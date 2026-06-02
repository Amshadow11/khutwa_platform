<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIProvider
{
    private string $model;

    public function __construct()
    {
        $this->model = config('ai.models.openai', 'gpt-4o-mini');
    }

    /**
     * إرسال prompt والحصول على نص.
     *
     * @param string $prompt
     * @param array  $options خيارات إضافية (temperature, max_tokens, ...)
     *
     * @throws \Exception
     *
     * @return array ['content' => string, 'usage' => [...], 'model' => string]
     */
    public function complete(string $prompt, array $options = []): array
    {
        $response = OpenAI::chat()->create([
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens']  ?? 1500,
        ]);

        $choice = $response->choices[0];
        $usage  = $response->usage;

        return [
            'content'           => $choice->message->content,
            'model'             => $this->model,
            'prompt_tokens'     => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'total_tokens'      => $usage->totalTokens,
        ];
    }

    /**
     * إرسال prompt مع system message.
     */
    public function completeWithSystem(string $system, string $prompt, array $options = []): array
    {
        $response = OpenAI::chat()->create([
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens']  ?? 1500,
        ]);

        $choice = $response->choices[0];
        $usage  = $response->usage;

        return [
            'content'           => $choice->message->content,
            'model'             => $this->model,
            'prompt_tokens'     => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'total_tokens'      => $usage->totalTokens,
        ];
    }

    public function getModel(): string
    {
        return $this->model;
    }
}