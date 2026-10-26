<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?: config('services.gemini.key');
        $this->model = $model ?: config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Check if Gemini API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the configured model name.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Generate completion using Gemini AI.
     *
     * @param string $prompt
     * @param bool $jsonMode
     * @return string|null
     */
    public function generate(string $prompt, bool $jsonMode = false): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('Gemini AI requested but GEMINI_API_KEY is not configured in .env');
            return null;
        }

        $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $generationConfig = [
            'temperature' => 0.2,
            'maxOutputTokens' => 8192,
        ];

        if ($jsonMode) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        try {
            $response = Http::retry(3, 1000, function ($exception, $request) {
                return $exception->response && in_array($exception->response->status(), [429, 503]);
            })
            ->timeout(60)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => $generationConfig,
            ]);

            if ($response->successful()) {
                $candidates = $response->json('candidates');
                if (!empty($candidates[0]['content']['parts'][0]['text'])) {
                    return $candidates[0]['content']['parts'][0]['text'];
                }
            }

            Log::error('Gemini AI API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini AI Exception: ' . $e->getMessage());
        }

        return null;
    }
}
