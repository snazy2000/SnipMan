<?php

namespace App\Services;

use App\Models\AISetting;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private ?string $apiKey;

    private string $baseUrl;

    private string $model;

    private int $timeout;

    private int $maxTokens;

    private float $temperature;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? Config::get('ai.openai.api_key');
        $this->baseUrl = $config['base_url'] ?? Config::get('ai.openai.base_url', 'https://api.openai.com/v1');
        $this->model = $config['model'] ?? Config::get('ai.openai.model', 'gpt-4o-mini');
        $this->timeout = $config['timeout'] ?? Config::get('ai.openai.timeout', 30);
        $this->maxTokens = $config['max_tokens'] ?? Config::get('ai.openai.max_tokens', 512);
        $this->temperature = $config['temperature'] ?? Config::get('ai.openai.temperature', 0.1);
    }

    /**
     * Get configuration value from database first, then fallback to config/env
     */
    private function getConfigValue(string $key, $default = null)
    {
        try {
            return AISetting::get($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Generate a description for the given code
     */
    public function generateDescription(string $code, string $language): ?string
    {
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
     * Check if OpenAI is available
     */
    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $httpClient = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ]);

            if (app()->environment(['local', 'development']) || config('ai.openai.disable_ssl_verify', false)) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ]);
            }

            $response = $httpClient->get("{$this->baseUrl}/models");

            return $response->successful();
        } catch (Exception $e) {
            Log::warning('OpenAI connection failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $httpClient = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ]);

            if (app()->environment(['local', 'development']) || config('ai.openai.disable_ssl_verify', false)) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ]);
            }

            $response = $httpClient->get("{$this->baseUrl}/models");

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return collect($data['data'] ?? [])
                ->pluck('id')
                ->toArray();
        } catch (Exception $e) {
            Log::warning('Failed to fetch OpenAI models', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Make a request to OpenAI API
     */
    private function makeRequest(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API key not configured');

            return null;
        }

        try {
            $httpClient = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ]);

            if (app()->environment(['local', 'development']) || config('ai.openai.disable_ssl_verify', false)) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ]);
            }

            $response = $httpClient->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'stream' => false,
            ]);

            if (! $response->successful()) {
                $statusCode = $response->status();
                $responseBody = $response->body();

                throw new \Exception("OpenAI API request failed (HTTP {$statusCode}): {$responseBody}", $statusCode);
            }

            $data = $response->json();
            $result = trim($data['choices'][0]['message']['content'] ?? '');

            return ! empty($result) ? $result : null;
        } catch (Exception $e) {
            Log::error('OpenAI request exception', [
                'error' => $e->getMessage(),
                'model' => $this->model,
                'code' => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Analyze code and return all available insights
     */
    public function analyzeCode(string $code, string $language): array
    {
        $results = [
            'description' => null,
            'processed_at' => now(),
        ];

        if (! $this->isAvailable()) {
            return $results;
        }

        $results['description'] = $this->generateDescription($code, $language);

        return $results;
    }
}
