<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAiService
{
    protected string $model = 'openai/gpt-oss-20b';
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * Send a multi-turn chat conversation to Groq AI.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return string|null
     */
    public function chat(array $messages, array $options = []): ?string
    {
        $apiKey = config('services.groq.key');
        if (empty($apiKey)) {
            Log::warning('Groq AI requested but GROQ_API_KEY is not configured.');
            return null;
        }

        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.6,
            'max_tokens' => 2048,
        ], $options);

        try {
            $response = Http::retry(3, 1000, function ($exception, $request) {
                return $exception->response && $exception->response->status() === 429;
            })
            ->withToken($apiKey)
            ->timeout(25)
            ->post($this->baseUrl, $payload);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('Groq AI API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Groq AI Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    /**
     * Generate completion using Groq AI.
     * Includes retry logic to handle rate limits (HTTP 429).
     *
     * @param string $prompt
     * @return string|null
     */
    public function generate(string $prompt): ?string
    {
        return $this->chat([
            ['role' => 'user', 'content' => $prompt]
        ]);
    }
}
