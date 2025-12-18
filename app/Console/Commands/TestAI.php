<?php

namespace App\Console\Commands;

use App\Services\AIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test
                            {--provider= : Specific provider to test (ollama, openrouter)}
                            {--models : List available models}
                            {--code= : Test code snippet}
                            {--language=php : Programming language}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AI provider functionality and connectivity';

    /**
     * Execute the console command.
     */
    public function handle(AIService $aiService): int
    {
        $this->info('🤖 AI Provider Testing Tool');
        $this->line('');

        // Test specific provider if specified
        if ($provider = $this->option('provider')) {
            return $this->testProvider($provider, $aiService);
        }

        // Test all providers
        $providers = AIService::getAvailableProviders();

        foreach ($providers as $providerKey => $providerInfo) {
            $this->info("Testing {$providerInfo['name']} ({$providerKey}):");
            $this->testProvider($providerKey, $aiService);
            $this->line('');
        }

        return 0;
    }

    /**
     * Test a specific provider
     */
    private function testProvider(string $provider, AIService $aiService): int
    {
        try {
            // Switch to the provider
            $aiService->switchProvider($provider);

            $config = $aiService->getProviderConfig();
            $this->table(
                ['Setting', 'Value'],
                collect($config)->map(fn($value, $key) => [
                    $key,
                    $key === 'api_key' ? (empty($value) ? '❌ Not set' : '✅ Set') : $value
                ])->toArray()
            );

            // Test connectivity
            $this->line('Testing connectivity...');
            if ($aiService->isAvailable()) {
                $this->info('✅ Provider is available');
            } else {
                $this->error('❌ Provider is not available');
                return 1;
            }

            // List models if requested
            if ($this->option('models')) {
                $this->line('Fetching available models...');
                $models = $aiService->getAvailableModels();

                if (empty($models)) {
                    $this->warn('No models found or unable to fetch models');
                } else {
                    $this->info('Available models:');
                    foreach ($models as $model) {
                        $this->line("  • {$model}");
                    }
                }
            }

            // Test with code if provided
            if ($code = $this->option('code')) {
                $language = $this->option('language');
                $this->line("Testing code analysis with {$language} code...");

                // Enable features temporarily for testing
                Config::set('ai.features.auto_description', true);

                $results = $aiService->analyzeCode($code, $language);

                $this->table(
                    ['Analysis', 'Result'],
                    [
                        ['Description', $results['description'] ?? 'Not generated'],
                        ['Processed At', $results['processed_at']->format('Y-m-d H:i:s')],
                    ]
                );
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error testing {$provider}: " . $e->getMessage());
            return 1;
        }
    }
}
