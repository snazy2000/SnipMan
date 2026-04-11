<?php

use App\Models\AISetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $providerExists = DB::table('ai_settings')
            ->where('key', 'ai.provider')
            ->exists();

        if ($providerExists) {
            DB::table('ai_settings')
                ->where('key', 'ai.provider')
                ->update([
                    'validation_rules' => json_encode(['required', 'in:ollama,openrouter,openai']),
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('ai_settings')->insert([
                'key' => 'ai.provider',
                'value' => 'ollama',
                'type' => 'string',
                'group' => 'general',
                'label' => 'AI Provider',
                'description' => 'The AI provider to use for code analysis',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'in:ollama,openrouter,openai']),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $openAISettings = [
            [
                'key' => 'ai.openai.api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'openai',
                'label' => 'API Key',
                'description' => 'OpenAI API key',
                'is_sensitive' => true,
                'is_required' => false,
                'validation_rules' => json_encode(['nullable', 'string']),
                'sort_order' => 1,
            ],
            [
                'key' => 'ai.openai.base_url',
                'value' => 'https://api.openai.com/v1',
                'type' => 'string',
                'group' => 'openai',
                'label' => 'Base URL',
                'description' => 'OpenAI API base URL',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'url']),
                'sort_order' => 2,
            ],
            [
                'key' => 'ai.openai.model',
                'value' => 'gpt-4o-mini',
                'type' => 'string',
                'group' => 'openai',
                'label' => 'Model',
                'description' => 'OpenAI model to use',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'string']),
                'sort_order' => 3,
            ],
            [
                'key' => 'ai.openai.timeout',
                'value' => '30',
                'type' => 'integer',
                'group' => 'openai',
                'label' => 'Timeout (seconds)',
                'description' => 'Request timeout in seconds',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:5', 'max:300']),
                'sort_order' => 4,
            ],
            [
                'key' => 'ai.openai.max_tokens',
                'value' => '512',
                'type' => 'integer',
                'group' => 'openai',
                'label' => 'Max Tokens',
                'description' => 'Maximum tokens in response',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:50', 'max:4096']),
                'sort_order' => 5,
            ],
            [
                'key' => 'ai.openai.temperature',
                'value' => '0.1',
                'type' => 'float',
                'group' => 'openai',
                'label' => 'Temperature',
                'description' => 'Creativity level (0.0 - 1.0)',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'numeric', 'min:0', 'max:1']),
                'sort_order' => 6,
            ],
            [
                'key' => 'ai.openai.disable_ssl_verify',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'openai',
                'label' => 'Disable SSL Verification',
                'description' => 'Disable SSL certificate verification (for development only)',
                'is_sensitive' => false,
                'is_required' => false,
                'validation_rules' => json_encode(['boolean']),
                'sort_order' => 7,
            ],
        ];

        foreach ($openAISettings as $setting) {
            DB::table('ai_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        AISetting::clearCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ai_settings')
            ->whereIn('key', [
                'ai.openai.api_key',
                'ai.openai.base_url',
                'ai.openai.model',
                'ai.openai.timeout',
                'ai.openai.max_tokens',
                'ai.openai.temperature',
                'ai.openai.disable_ssl_verify',
            ])
            ->delete();

        DB::table('ai_settings')
            ->where('key', 'ai.provider')
            ->update([
                'validation_rules' => json_encode(['required', 'in:ollama,openrouter']),
                'updated_at' => now(),
            ]);

        AISetting::clearCache();
    }
};
