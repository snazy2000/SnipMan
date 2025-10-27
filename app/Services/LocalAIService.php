<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class LocalAIService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;
    private int $maxTokens;

    public function __construct()
    {
        $this->baseUrl = Config::get('ai.ollama.base_url');
        $this->model = Config::get('ai.ollama.model');
        $this->timeout = Config::get('ai.ollama.timeout');
        $this->maxTokens = Config::get('ai.ollama.max_tokens');
    }

    /**
     * Generate a description for the given code
     */
    public function generateDescription(string $code, string $language): ?string
    {
        if (!Config::get('ai.features.auto_description')) {
            return null;
        }

        $prompt = str_replace(
            ['{language}', '{code}'],
            [$language, $code],
            Config::get('ai.prompts.description')
        );

        return $this->makeRequest($prompt);
    }

    /**
     * Generate smart tags for the given code
     */
    public function generateTags(string $code, string $language): array
    {
        if (!Config::get('ai.features.smart_tagging')) {
            return [];
        }

        $prompt = str_replace(
            ['{language}', '{code}'],
            [$language, $code],
            Config::get('ai.prompts.tags')
        );

        $response = $this->makeRequest($prompt);

        if (!$response) {
            return [];
        }

        // Parse comma-separated tags
        $tags = array_map('trim', explode(',', $response));
        return array_filter($tags, fn($tag) => !empty($tag));
    }

    /**
     * Generate a quality score for the given code
     */
    public function generateQualityScore(string $code, string $language): ?int
    {
        if (!Config::get('ai.features.quality_scoring')) {
            return null;
        }

        $prompt = str_replace(
            ['{language}', '{code}'],
            [$language, $code],
            Config::get('ai.prompts.quality')
        );

        $response = $this->makeRequest($prompt);

        if (!$response) {
            return null;
        }

        // Extract numeric score
        $score = (int) filter_var($response, FILTER_SANITIZE_NUMBER_INT);
        return ($score >= 1 && $score <= 10) ? $score : null;
    }

    /**
     * Check if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('Ollama connection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/tags");

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            return collect($data['models'] ?? [])
                ->pluck('name')
                ->toArray();
        } catch (Exception $e) {
            Log::warning('Failed to fetch Ollama models', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Make a request to Ollama API
     */
    private function makeRequest(string $prompt): ?string
    {
        try {
            Log::info('Making AI request', [
                'model' => $this->model,
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/generate", [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => [
                        'num_predict' => $this->maxTokens,
                        'temperature' => 0.1, // Lower temperature for more consistent results
                        'top_p' => 0.9,
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('Ollama API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            $result = trim($data['response'] ?? '');

            Log::info('AI request completed', [
                'response_length' => strlen($result),
                'done' => $data['done'] ?? false
            ]);

            return !empty($result) ? $result : null;

        } catch (Exception $e) {
            Log::error('AI request exception', [
                'error' => $e->getMessage(),
                'model' => $this->model
            ]);
            return null;
        }
    }

    /**
     * Analyze code and return all available insights
     */
    public function analyzeCode(string $code, string $language): array
    {
        $results = [
            'description' => null,
            'tags' => [],
            'quality_score' => null,
            'processed_at' => now(),
        ];

        // Only proceed if Ollama is available
        if (!$this->isAvailable()) {
            Log::warning('Ollama not available for code analysis');
            return $results;
        }

        // Generate description
        if (Config::get('ai.features.auto_description')) {
            $results['description'] = $this->generateDescription($code, $language);
        }

        // Generate tags
        if (Config::get('ai.features.smart_tagging')) {
            $results['tags'] = $this->generateTags($code, $language);
        }

        // Generate quality score
        if (Config::get('ai.features.quality_scoring')) {
            $results['quality_score'] = $this->generateQualityScore($code, $language);
        }

        return $results;
    }
}
