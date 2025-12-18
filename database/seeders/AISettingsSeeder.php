<?php

namespace Database\Seeders;

use App\Models\AISetting;
use Illuminate\Database\Seeder;

class AISettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General AI Settings
            [
                'key' => 'ai.provider',
                'value' => 'ollama',
                'type' => 'string',
                'group' => 'general',
                'label' => 'AI Provider',
                'description' => 'The AI provider to use for code analysis',
                'is_required' => true,
                'validation_rules' => ['required', 'in:ollama,openrouter'],
                'sort_order' => 1,
            ],

            // Ollama Settings
            [
                'key' => 'ai.ollama.base_url',
                'value' => 'http://localhost:11434',
                'type' => 'string',
                'group' => 'ollama',
                'label' => 'Ollama URL',
                'description' => 'Base URL for Ollama API',
                'is_required' => true,
                'validation_rules' => ['required', 'url'],
                'sort_order' => 1,
            ],
            [
                'key' => 'ai.ollama.model',
                'value' => 'codellama:7b',
                'type' => 'string',
                'group' => 'ollama',
                'label' => 'Ollama Model',
                'description' => 'Model name to use with Ollama',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'ai.ollama.timeout',
                'value' => '30',
                'type' => 'integer',
                'group' => 'ollama',
                'label' => 'Timeout (seconds)',
                'description' => 'Request timeout in seconds',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:5', 'max:300'],
                'sort_order' => 3,
            ],
            [
                'key' => 'ai.ollama.max_tokens',
                'value' => '512',
                'type' => 'integer',
                'group' => 'ollama',
                'label' => 'Max Tokens',
                'description' => 'Maximum tokens in response',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:50', 'max:4096'],
                'sort_order' => 4,
            ],

            // OpenRouter Settings
            [
                'key' => 'ai.openrouter.api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'openrouter',
                'label' => 'API Key',
                'description' => 'OpenRouter API key',
                'is_sensitive' => true,
                'is_required' => false,
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'ai.openrouter.base_url',
                'value' => 'https://openrouter.ai/api/v1',
                'type' => 'string',
                'group' => 'openrouter',
                'label' => 'Base URL',
                'description' => 'OpenRouter API base URL',
                'is_required' => true,
                'validation_rules' => ['required', 'url'],
                'sort_order' => 2,
            ],
            [
                'key' => 'ai.openrouter.model',
                'value' => 'anthropic/claude-3.5-haiku',
                'type' => 'string',
                'group' => 'openrouter',
                'label' => 'Model',
                'description' => 'OpenRouter model to use',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 3,
            ],
            [
                'key' => 'ai.openrouter.timeout',
                'value' => '30',
                'type' => 'integer',
                'group' => 'openrouter',
                'label' => 'Timeout (seconds)',
                'description' => 'Request timeout in seconds',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:5', 'max:300'],
                'sort_order' => 4,
            ],
            [
                'key' => 'ai.openrouter.max_tokens',
                'value' => '512',
                'type' => 'integer',
                'group' => 'openrouter',
                'label' => 'Max Tokens',
                'description' => 'Maximum tokens in response',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:50', 'max:4096'],
                'sort_order' => 5,
            ],
            [
                'key' => 'ai.openrouter.temperature',
                'value' => '0.1',
                'type' => 'float',
                'group' => 'openrouter',
                'label' => 'Temperature',
                'description' => 'Creativity level (0.0 - 1.0)',
                'is_required' => true,
                'validation_rules' => ['required', 'numeric', 'min:0', 'max:1'],
                'sort_order' => 6,
            ],
            [
                'key' => 'ai.openrouter.top_p',
                'value' => '0.9',
                'type' => 'float',
                'group' => 'openrouter',
                'label' => 'Top P',
                'description' => 'Nucleus sampling parameter',
                'is_required' => true,
                'validation_rules' => ['required', 'numeric', 'min:0', 'max:1'],
                'sort_order' => 7,
            ],
            [
                'key' => 'ai.openrouter.site_url',
                'value' => config('app.url', 'http://localhost'),
                'type' => 'string',
                'group' => 'openrouter',
                'label' => 'Site URL',
                'description' => 'Your application URL for OpenRouter',
                'is_required' => true,
                'validation_rules' => ['required', 'url'],
                'sort_order' => 8,
            ],
            [
                'key' => 'ai.openrouter.site_name',
                'value' => config('app.name', 'Snippet Manager'),
                'type' => 'string',
                'group' => 'openrouter',
                'label' => 'Site Name',
                'description' => 'Your application name for OpenRouter',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 9,
            ],
            [
                'key' => 'ai.openrouter.disable_ssl_verify',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'openrouter',
                'label' => 'Disable SSL Verification',
                'description' => 'Disable SSL certificate verification (for development only)',
                'is_required' => false,
                'validation_rules' => ['boolean'],
                'sort_order' => 10,
            ],

            // Feature Settings
            [
                'key' => 'ai.features.auto_description',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Auto Description',
                'description' => 'Automatically generate descriptions for code snippets',
                'is_required' => false,
                'validation_rules' => ['boolean'],
                'sort_order' => 1,
            ],

            // Processing Settings
            [
                'key' => 'ai.processing.queue',
                'value' => 'default',
                'type' => 'string',
                'group' => 'processing',
                'label' => 'Queue Name',
                'description' => 'Queue to use for AI processing jobs',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'ai.processing.retry_attempts',
                'value' => '3',
                'type' => 'integer',
                'group' => 'processing',
                'label' => 'Retry Attempts',
                'description' => 'Number of retry attempts for failed jobs',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:1', 'max:10'],
                'sort_order' => 2,
            ],
            [
                'key' => 'ai.processing.retry_delay',
                'value' => '60',
                'type' => 'integer',
                'group' => 'processing',
                'label' => 'Retry Delay (seconds)',
                'description' => 'Delay between retry attempts',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:10', 'max:3600'],
                'sort_order' => 3,
            ],
        ];

        foreach ($settings as $setting) {
            AISetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
