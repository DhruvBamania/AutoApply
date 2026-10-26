<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAiService
{
    protected string $model = 'openai/gpt-oss-20b';
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * Generate completion using Groq AI.
     * Includes retry logic to handle rate limits (HTTP 429).
     *
     * @param string $prompt
     * @return string|null
     */
    public function generate(string $prompt): ?string
    {
        $response = Http::retry(3, 1000, function ($exception, $request) {
            return $exception->response && $exception->response->status() === 429;
        })
        ->withToken(config('services.groq.key')) // Ensure this is set in config/services.php
        ->post($this->baseUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 4096,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::error('Groq AI API Request Failed', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }
}
