<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the AI provider used for code analysis.
    | Currently supports 'ollama' for local LLM integration.
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
        'timeout' => env('OLLAMA_TIMEOUT', 30), // seconds
        'max_tokens' => env('OLLAMA_MAX_TOKENS', 512),
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
        'smart_tagging' => env('AI_SMART_TAGGING', false),
        'quality_scoring' => env('AI_QUALITY_SCORING', false),
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
        'retry_delay' => env('AI_RETRY_DELAY', 60), // seconds
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
        'tags' => 'Analyze this {language} code and suggest 3-5 relevant tags. Include framework, patterns, and functionality. Return only the tags separated by commas:\n\n{code}',
        'quality' => 'Rate this {language} code quality from 1-10 considering readability, efficiency, and best practices. Return only the number:\n\n{code}',
    ],
];
