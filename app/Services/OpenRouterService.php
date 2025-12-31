<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\AISetting;
use Exception;

class OpenRouterService
{
    private ?string $apiKey;
    private string $baseUrl;
    private string $model;
    private int $timeout;
    private int $maxTokens;
    private float $temperature;
    private float $topP;
    private string $siteUrl;
    private string $siteName;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? Config::get('ai.openrouter.api_key');
        $this->baseUrl = $config['base_url'] ?? Config::get('ai.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->model = $config['model'] ?? Config::get('ai.openrouter.model', 'anthropic/claude-3.5-haiku');
        $this->timeout = $config['timeout'] ?? Config::get('ai.openrouter.timeout', 30);
        $this->maxTokens = $config['max_tokens'] ?? Config::get('ai.openrouter.max_tokens', 512);
        $this->temperature = $config['temperature'] ?? Config::get('ai.openrouter.temperature', 0.1);
        $this->topP = $config['top_p'] ?? Config::get('ai.openrouter.top_p', 0.9);
        $this->siteUrl = $config['site_url'] ?? Config::get('ai.openrouter.site_url', Config::get('app.url', 'http://localhost'));
        $this->siteName = $config['site_name'] ?? Config::get('ai.openrouter.site_name', Config::get('app.name', 'Snippet Manager'));
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
        if (!$enabled) {
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
     * Check if OpenRouter is available
     */
    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            Log::info('OpenRouter not available: API key is empty', [
                'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
                'base_url' => $this->baseUrl,
            ]);
            return false;
        }

        try {
            Log::info('Testing OpenRouter connection', [
                'url' => "{$this->baseUrl}/models",
                'api_key_prefix' => substr($this->apiKey, 0, 10) . '...',
                'timeout' => 5,
                'environment' => app()->environment(),
            ]);

            // Create HTTP client with SSL options for Windows development
            $httpClient = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ]);

            // For development/Windows environments, allow bypassing SSL verification
            if (app()->environment(['local', 'development']) || config('ai.openrouter.disable_ssl_verify', false)) {
                Log::info('OpenRouter: Disabling SSL verification for development environment');
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $response = $httpClient->get("{$this->baseUrl}/models");

            $success = $response->successful();

            Log::info('OpenRouter connection test result', [
                'success' => $success,
                'status_code' => $response->status(),
                'response_size' => strlen($response->body()),
                'response_headers' => $response->headers(),
                'response_body_preview' => substr($response->body(), 0, 200),
            ]);

            if (!$success) {
                Log::warning('OpenRouter connection failed - HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);
            }

            return $success;
        } catch (Exception $e) {
            Log::error('OpenRouter connection failed - Exception', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'api_key_set' => !empty($this->apiKey),
                'base_url' => $this->baseUrl,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        if (empty($this->apiKey)) {
            Log::info('OpenRouter getAvailableModels: API key is empty');
            return [];
        }

        try {
            Log::info('Fetching OpenRouter models', [
                'url' => "{$this->baseUrl}/models",
                'timeout' => 10,
            ]);

            // Create HTTP client with SSL options for Windows development
            $httpClient = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ]);

            // For development/Windows environments, allow bypassing SSL verification
            if (app()->environment(['local', 'development']) || config('ai.openrouter.disable_ssl_verify', false)) {
                Log::info('OpenRouter getAvailableModels: Disabling SSL verification for development environment');
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $response = $httpClient->get("{$this->baseUrl}/models");

            if (!$response->successful()) {
                Log::warning('OpenRouter models fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            $models = collect($data['data'] ?? [])->pluck('id')->toArray();

            Log::info('OpenRouter models fetched successfully', [
                'model_count' => count($models),
                'sample_models' => array_slice($models, 0, 5),
            ]);

            return $models;
        } catch (Exception $e) {
            Log::error('Failed to fetch OpenRouter models - Exception', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
            ]);
            return [];
        }
    }

    /**
     * Make a request to OpenRouter API
     */
    private function makeRequest(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenRouter API key not configured');
            return null;
        }

        try {
            Log::info('Making OpenRouter AI request', [
                'model' => $this->model,
                'prompt_length' => strlen($prompt)
            ]);

            // Create HTTP client with SSL options for Windows development
            $httpClient = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => $this->siteUrl,
                    'X-Title' => $this->siteName,
                ]);

            // For development/Windows environments, allow bypassing SSL verification
            if (app()->environment(['local', 'development']) || config('ai.openrouter.disable_ssl_verify', false)) {
                Log::info('OpenRouter makeRequest: Disabling SSL verification');
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $response = $httpClient->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'stream' => false,
            ]);

            if (!$response->successful()) {
                $statusCode = $response->status();
                $responseBody = $response->body();

                // Handle rate limiting specifically - throw exception to trigger job retry
                if ($statusCode === 429) {
                    Log::warning('OpenRouter API rate limited', [
                        'model' => $this->model,
                        'status' => $statusCode,
                        'response' => $responseBody,
                        'retry_suggestion' => 'Model is temporarily rate-limited. Consider switching to a different model or adding your own API key.'
                    ]);

                    // Throw exception for rate limits to trigger job failure/retry
                    throw new \Exception("OpenRouter API rate limited (HTTP 429): {$responseBody}", 429);
                } else {
                    Log::error('OpenRouter API request failed', [
                        'model' => $this->model,
                        'status' => $statusCode,
                        'body' => $responseBody
                    ]);

                    // Throw exception for other HTTP errors too
                    throw new \Exception("OpenRouter API request failed (HTTP {$statusCode}): {$responseBody}", $statusCode);
                }
            }

            $data = $response->json();
            $result = trim($data['choices'][0]['message']['content'] ?? '');

            Log::info('OpenRouter AI request completed', [
                'response_length' => strlen($result),
                'usage' => $data['usage'] ?? null
            ]);

            return !empty($result) ? $result : null;

        } catch (Exception $e) {
            Log::error('OpenRouter AI request exception', [
                'error' => $e->getMessage(),
                'model' => $this->model,
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception so the job can handle it properly
            throw $e;
        }
    }

    /**
     * Analyze code and return all available insights
     */
    public function analyzeCode(string $code, string $language): array
    {
        Log::info('OpenRouterService: Starting code analysis', [
            'language' => $language,
            'code_length' => strlen($code),
            'model' => $this->model,
            'features_enabled' => [
                'auto_description' => $this->getConfigValue('ai.features.auto_description', Config::get('ai.features.auto_description')),
            ]
        ]);

        $results = [
            'description' => null,
            'processed_at' => now(),
        ];

        // Only proceed if OpenRouter is available
        if (!$this->isAvailable()) {
            Log::warning('OpenRouterService: OpenRouter not available for code analysis', [
                'base_url' => $this->baseUrl,
                'model' => $this->model,
                'has_api_key' => !empty($this->apiKey)
            ]);
            return $results;
        }

        Log::info('OpenRouterService: OpenRouter is available, generating description');

        // Generate description
        $startTime = microtime(true);
        $results['description'] = $this->generateDescription($code, $language);
        $descriptionTime = microtime(true) - $startTime;

        Log::info('OpenRouterService: Description generation completed', [
            'time_seconds' => round($descriptionTime, 2),
            'has_description' => !empty($results['description']),
            'description_length' => !empty($results['description']) ? strlen($results['description']) : 0
        ]);

        return $results;
    }

    /**
     * Get recommended models for better reliability
     */
    public function getRecommendedModels(): array
    {
        return [
            // Reliable paid models
            'anthropic/claude-3.5-haiku' => [
                'name' => 'Claude 3.5 Haiku',
                'type' => 'paid',
                'reliability' => 'high',
                'cost' => 'low'
            ],
            'openai/gpt-4o-mini' => [
                'name' => 'GPT-4o Mini',
                'type' => 'paid',
                'reliability' => 'high',
                'cost' => 'low'
            ],
            'anthropic/claude-3-haiku' => [
                'name' => 'Claude 3 Haiku',
                'type' => 'paid',
                'reliability' => 'high',
                'cost' => 'low'
            ],
            // Free models (can be rate-limited)
            'microsoft/phi-3-mini-128k-instruct:free' => [
                'name' => 'Phi-3 Mini (Free)',
                'type' => 'free',
                'reliability' => 'medium',
                'cost' => 'free'
            ],
            'qwen/qwen-2-7b-instruct:free' => [
                'name' => 'Qwen 2 7B (Free)',
                'type' => 'free',
                'reliability' => 'medium',
                'cost' => 'free'
            ],
        ];
    }

    /**
     * Check if the current or specified model is a free model that might be rate-limited
     */
    public function isUsingFreeModel($model = null): bool
    {
        $modelToCheck = $model ?? $this->model;
        $freeModelPatterns = [':free', 'free', 'qwen/', 'microsoft/phi'];

        foreach ($freeModelPatterns as $pattern) {
            if (str_contains(strtolower($modelToCheck), $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get rate limit advice for current model
     */
    public function getRateLimitAdvice(): array
    {
        $advice = [
            'is_free_model' => $this->isUsingFreeModel(),
            'current_model' => $this->model,
            'recommended_actions' => []
        ];

        if ($this->isUsingFreeModel()) {
            $advice['recommended_actions'] = [
                'Switch to a paid model like "anthropic/claude-3.5-haiku" for better reliability',
                'Add your own API key at https://openrouter.ai/settings/integrations',
                'Wait a few minutes and try again',
                'Consider using fewer AI features to reduce API calls'
            ];
        } else {
            $advice['recommended_actions'] = [
                'Check your OpenRouter account balance',
                'Verify your API key is active',
                'Wait a moment and try again'
            ];
        }

        return $advice;
    }
}
