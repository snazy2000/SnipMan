<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the AI provider used for code analysis.
    | Supported providers: 'ollama', 'openrouter'
    |
    */

    'provider' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ollama local LLM integration
    |
    */

    'ollama' => [
        'base_url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'codellama:7b'),
        'timeout' => env('OLLAMA_TIMEOUT', 30),
        'max_tokens' => env('OLLAMA_MAX_TOKENS', 512),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenRouter API integration
    |
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3.5-haiku'),
        'timeout' => env('OPENROUTER_TIMEOUT', 30),
        'max_tokens' => env('OPENROUTER_MAX_TOKENS', 512),
        'temperature' => env('OPENROUTER_TEMPERATURE', 0.1),
        'top_p' => env('OPENROUTER_TOP_P', 0.9),
        'site_url' => env('APP_URL', 'http://localhost'),
        'site_name' => env('APP_NAME', config('app.name', 'Snippet Manager')),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Features Toggle
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific AI features
    |
    */

    'features' => [
        'auto_description' => env('AI_AUTO_DESCRIPTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Processing Settings
    |--------------------------------------------------------------------------
    |
    | Settings for AI processing jobs and queues
    |
    */

    'processing' => [
        'queue' => env('AI_QUEUE', 'default'),
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('AI_RETRY_DELAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Prompts
    |--------------------------------------------------------------------------
    |
    | Customizable prompts for different AI tasks
    |
    */

    'prompts' => [
        'description' => 'Analyze this {language} code and provide a brief, clear description (1-2 sentences) of what it does. Focus on the main functionality and purpose:\n\n{code}',
    ],
];
