<?php

namespace App\Services;

use App\Models\AISetting;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocalAIService
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    private int $maxTokens;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? Config::get('ai.ollama.base_url', 'http://localhost:11434');
        $this->model = $config['model'] ?? Config::get('ai.ollama.model', 'codellama:7b');
        $this->timeout = $config['timeout'] ?? Config::get('ai.ollama.timeout', 30);
        $this->maxTokens = $config['max_tokens'] ?? Config::get('ai.ollama.max_tokens', 512);
    }

    /**
     * Get configuration value from database first, then fallback to config/env
     */
    private function getConfigValue(string $key, $default = null)
    {
        try {
            // Try to get from database first
            return AISetting::get($key, $default);
        } catch (\Exception $e) {
            // If database is not available or table doesn't exist, use default
            return $default;
        }
    }

    /**
     * Generate a description for the given code
     */
    public function generateDescription(string $code, string $language): ?string
    {
        // Check if feature is enabled in database first, then config
        $enabled = $this->getConfigValue('ai.features.auto_description', Config::get('ai.features.auto_description'));
        if (! $enabled) {
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

            if (! $response->successful()) {
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
                'prompt_length' => strlen($prompt),
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
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Ollama API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();
            $result = trim($data['response'] ?? '');

            Log::info('AI request completed', [
                'response_length' => strlen($result),
                'done' => $data['done'] ?? false,
            ]);

            return ! empty($result) ? $result : null;

        } catch (Exception $e) {
            Log::error('AI request exception', [
                'error' => $e->getMessage(),
                'model' => $this->model,
            ]);

            return null;
        }
    }

    /**
     * Analyze code and return all available insights
     */
    public function analyzeCode(string $code, string $language): array
    {
        Log::info('LocalAIService: Starting code analysis', [
            'language' => $language,
            'code_length' => strlen($code),
            'model' => $this->model,
            'features_enabled' => [
                'auto_description' => $this->getConfigValue('ai.features.auto_description', Config::get('ai.features.auto_description')),
            ],
        ]);

        $results = [
            'description' => null,
            'processed_at' => now(),
        ];

        // Only proceed if Ollama is available
        if (! $this->isAvailable()) {
            Log::warning('LocalAIService: Ollama not available for code analysis', [
                'base_url' => $this->baseUrl,
                'model' => $this->model,
            ]);

            return $results;
        }

        Log::info('LocalAIService: Ollama is available, generating description');

        // Generate description
        $startTime = microtime(true);
        $results['description'] = $this->generateDescription($code, $language);
        $descriptionTime = microtime(true) - $startTime;

        Log::info('LocalAIService: Description generation completed', [
            'time_seconds' => round($descriptionTime, 2),
            'has_description' => ! empty($results['description']),
            'description_length' => ! empty($results['description']) ? strlen($results['description']) : 0,
        ]);

        return $results;
    }
}
