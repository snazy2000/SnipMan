<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use App\Services\LocalAIService;
use App\Services\OpenRouterService;
use App\Models\AISetting;
use InvalidArgumentException;

class AIService
{
    private LocalAIService|OpenRouterService $provider;
    private string $providerName;

    public function __construct()
    {
        $this->providerName = $this->getConfigValue('ai.provider', Config::get('ai.provider', 'ollama'));
        $this->provider = $this->createProvider($this->providerName);
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
     * Create the appropriate AI provider instance
     */
    private function createProvider(string $provider): LocalAIService|OpenRouterService
    {
        return match ($provider) {
            'ollama' => new LocalAIService([
                'base_url' => $this->getConfigValue('ai.ollama.base_url', Config::get('ai.ollama.base_url')),
                'model' => $this->getConfigValue('ai.ollama.model', Config::get('ai.ollama.model')),
                'timeout' => $this->getConfigValue('ai.ollama.timeout', Config::get('ai.ollama.timeout')),
                'max_tokens' => $this->getConfigValue('ai.ollama.max_tokens', Config::get('ai.ollama.max_tokens')),
            ]),
            'openrouter' => new OpenRouterService([
                'api_key' => $this->getConfigValue('ai.openrouter.api_key', Config::get('ai.openrouter.api_key')),
                'base_url' => $this->getConfigValue('ai.openrouter.base_url', Config::get('ai.openrouter.base_url')),
                'model' => $this->getConfigValue('ai.openrouter.model', Config::get('ai.openrouter.model')),
                'timeout' => $this->getConfigValue('ai.openrouter.timeout', Config::get('ai.openrouter.timeout')),
                'max_tokens' => $this->getConfigValue('ai.openrouter.max_tokens', Config::get('ai.openrouter.max_tokens')),
                'temperature' => $this->getConfigValue('ai.openrouter.temperature', Config::get('ai.openrouter.temperature')),
                'top_p' => $this->getConfigValue('ai.openrouter.top_p', Config::get('ai.openrouter.top_p')),
                'site_url' => $this->getConfigValue('ai.openrouter.site_url', Config::get('ai.openrouter.site_url')),
                'site_name' => $this->getConfigValue('ai.openrouter.site_name', Config::get('ai.openrouter.site_name')),
            ]),
            default => throw new InvalidArgumentException("Unsupported AI provider: {$provider}")
        };
    }

    /**
     * Generate a description for the given code
     */
    public function generateDescription(string $code, string $language): ?string
    {
        return $this->provider->generateDescription($code, $language);
    }

    /**
     * Check if the AI provider is available
     */
    public function isAvailable(): bool
    {
        return $this->provider->isAvailable();
    }

    /**
     * Get available models from the current provider
     */
    public function getAvailableModels(): array
    {
        return $this->provider->getAvailableModels();
    }

    /**
     * Analyze code and return all available insights
     */
    public function analyzeCode(string $code, string $language): array
    {
        return $this->provider->analyzeCode($code, $language);
    }

    /**
     * Get the current provider name
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * Get the current provider instance
     */
    public function getProvider(): LocalAIService|OpenRouterService
    {
        return $this->provider;
    }

    /**
     * Switch to a different provider
     */
    public function switchProvider(string $provider): void
    {
        $this->providerName = $provider;
        $this->provider = $this->createProvider($provider);
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(): array
    {
        return match ($this->providerName) {
            'ollama' => [
                'base_url' => $this->getConfigValue('ai.ollama.base_url', Config::get('ai.ollama.base_url')),
                'model' => $this->getConfigValue('ai.ollama.model', Config::get('ai.ollama.model')),
                'timeout' => $this->getConfigValue('ai.ollama.timeout', Config::get('ai.ollama.timeout')),
                'max_tokens' => $this->getConfigValue('ai.ollama.max_tokens', Config::get('ai.ollama.max_tokens')),
            ],
            'openrouter' => [
                'api_key' => $this->getConfigValue('ai.openrouter.api_key', Config::get('ai.openrouter.api_key')),
                'base_url' => $this->getConfigValue('ai.openrouter.base_url', Config::get('ai.openrouter.base_url')),
                'model' => $this->getConfigValue('ai.openrouter.model', Config::get('ai.openrouter.model')),
                'timeout' => $this->getConfigValue('ai.openrouter.timeout', Config::get('ai.openrouter.timeout')),
                'max_tokens' => $this->getConfigValue('ai.openrouter.max_tokens', Config::get('ai.openrouter.max_tokens')),
                'temperature' => $this->getConfigValue('ai.openrouter.temperature', Config::get('ai.openrouter.temperature')),
                'top_p' => $this->getConfigValue('ai.openrouter.top_p', Config::get('ai.openrouter.top_p')),
                'site_url' => $this->getConfigValue('ai.openrouter.site_url', Config::get('ai.openrouter.site_url')),
                'site_name' => $this->getConfigValue('ai.openrouter.site_name', Config::get('ai.openrouter.site_name')),
            ],
            default => []
        };
    }

    /**
     * Refresh the provider (useful after settings changes)
     */
    public function refreshProvider(): void
    {
        $this->providerName = $this->getConfigValue('ai.provider', Config::get('ai.provider', 'ollama'));
        $this->provider = $this->createProvider($this->providerName);
    }

    /**
     * Get all available providers
     */
    public static function getAvailableProviders(): array
    {
        return [
            'ollama' => [
                'name' => 'Ollama',
                'description' => 'Local LLM integration using Ollama',
                'requires_api_key' => false,
            ],
            'openrouter' => [
                'name' => 'OpenRouter',
                'description' => 'Cloud-based AI models via OpenRouter',
                'requires_api_key' => true,
            ],
        ];
    }
}
