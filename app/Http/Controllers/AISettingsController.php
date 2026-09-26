<?php

namespace App\Http\Controllers;

use App\Models\AISetting;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AISettingsController extends Controller
{
    public function index(AIService $aiService)
    {
        $currentProvider = AISetting::get('ai.provider', 'ollama');
        $providers = AIService::getAvailableProviders();

        // Get settings grouped by category
        $generalSettings = AISetting::getByGroup('general');
        $ollamaSettings = AISetting::getByGroup('ollama');
        $openrouterSettings = AISetting::getByGroup('openrouter');
        $openaiSettings = AISetting::getByGroup('openai');
        $featureSettings = AISetting::getByGroup('features');
        $processingSettings = AISetting::getByGroup('processing');

        // Test current provider
        $aiService->switchProvider($currentProvider);
        $isAvailable = $aiService->isAvailable();

        // Get provider-specific information
        $providerInfo = [];
        if ($currentProvider === 'openrouter' && $isAvailable) {
            $provider = $aiService->getProvider();
            if (method_exists($provider, 'getRateLimitAdvice')) {
                $currentModel = AISetting::get('ai.openrouter.model', 'unknown');
                $providerInfo = [
                    'rate_limit_advice' => $provider->getRateLimitAdvice(),
                    'recommended_models' => $provider->getRecommendedModels(),
                    'current_model' => $currentModel,
                    'is_free_model' => $provider->isUsingFreeModel($currentModel),
                ];
            }
        }

        return view('ai.settings', compact(
            'currentProvider',
            'providers',
            'generalSettings',
            'ollamaSettings',
            'openrouterSettings',
            'openaiSettings',
            'featureSettings',
            'processingSettings',
            'isAvailable',
            'providerInfo'
        ));
    }

    public function update(Request $request)
    {
        // Validate all settings
        $validationRules = [];
        $settings = AISetting::all();

        foreach ($settings as $setting) {
            $formFieldName = str_replace('.', '_', $setting->key);
            if (! $request->has($formFieldName)) {
                continue;
            }

            // Merge the row's own rules with rules defined in code. Relying on
            // the column alone meant any row with an empty validation_rules was
            // saved unchecked - including provider names and base URLs.
            $rules = array_values(array_unique(array_merge(
                $setting->validation_rules ?? [],
                $this->baselineRulesFor($setting->key),
            )));

            if ($rules !== []) {
                $validationRules[$formFieldName] = $rules;
            }
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update settings
        $updated = 0;
        foreach ($settings as $setting) {
            $newValue = null;
            $shouldUpdate = false;

            // Convert dots to underscores for form field lookup
            $formFieldName = str_replace('.', '_', $setting->key);

            if ($setting->type === 'boolean') {
                // For boolean fields, handle checkbox behavior
                $newValue = $request->has($formFieldName) ? true : false;
                $shouldUpdate = true;
            } elseif ($request->has($formFieldName)) {
                // For non-boolean fields, only update if present in request
                $newValue = $request->input($formFieldName);
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $oldValue = $setting->value;
                AISetting::set($setting->key, $newValue);
                $updated++;

                Log::info('AI setting changed', [
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()->email,
                    'setting_key' => $setting->key,
                    'old_value' => $setting->is_sensitive ? '***' : $oldValue,
                    'new_value' => $setting->is_sensitive ? '***' : $newValue,
                ]);
            }
        }

        // Clear the AISetting cache so new database values are picked up.
        // Deliberately not config:clear - that drops the production config cache
        // mid-request and degrades every request until it is rebuilt.
        AISetting::clearCache();

        // Clear any cached service instances to pick up new provider
        app()->forgetInstance(AIService::class);

        // Restart queue workers to pick up new settings
        $queueRestarted = false;
        try {
            // For database/redis queues, this writes a restart signal to shared storage
            Artisan::call('queue:restart');
            $queueRestarted = true;
            Log::info('Queue restart signal sent after AI settings update');
        } catch (\Exception $e) {
            Log::warning('Failed to send queue restart signal after AI settings update', [
                'error' => $e->getMessage(),
            ]);
        }

        $message = "Updated {$updated} AI settings successfully.";
        if ($queueRestarted) {
            $message .= ' Queue workers will restart automatically to apply changes.';
        } else {
            $message .= ' Please manually restart your queue workers to apply changes.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Validation rules defined in code, keyed off the setting name.
     *
     * These are the guard rails that hold regardless of what the ai_settings row
     * says: a provider the application actually implements, and a base URL that
     * is a real http(s) URL rather than an arbitrary host a compromised admin
     * account could aim the AI client at.
     *
     * @return array<int, string>
     */
    private function baselineRulesFor(string $key): array
    {
        return match (true) {
            $key === 'ai.provider' => ['string', 'in:ollama,openrouter,openai'],
            str_ends_with($key, '.base_url') => ['nullable', 'string', 'url', 'starts_with:http://,https://', 'max:2048'],
            str_ends_with($key, '.site_url') => ['nullable', 'string', 'url', 'max:2048'],
            str_ends_with($key, '.api_key') => ['nullable', 'string', 'max:512'],
            str_ends_with($key, '.model'), str_ends_with($key, '.site_name') => ['nullable', 'string', 'max:255'],
            str_ends_with($key, '.temperature') => ['nullable', 'numeric', 'between:0,2'],
            str_ends_with($key, '.top_p') => ['nullable', 'numeric', 'between:0,1'],
            str_ends_with($key, '.timeout') => ['nullable', 'integer', 'min:1', 'max:600'],
            str_ends_with($key, '.max_tokens') => ['nullable', 'integer', 'min:1', 'max:32768'],
            default => [],
        };
    }

    public function resetToDefaults()
    {
        try {
            Log::warning('AI settings reset to defaults', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
            ]);

            // Run the seeder to reset all settings to defaults
            Artisan::call('db:seed', ['--class' => 'AISettingsSeeder']);

            // Clear AISetting cache
            AISetting::clearCache();

            // Clear any cached service instances
            app()->forgetInstance(AIService::class);

            // Restart queue workers to pick up new settings
            $queueRestarted = false;
            try {
                // For database/redis queues, this writes a restart signal to shared storage
                Artisan::call('queue:restart');
                $queueRestarted = true;
                Log::info('Queue restart signal sent after AI settings reset');
            } catch (\Exception $e) {
                Log::warning('Failed to send queue restart signal after AI settings reset', [
                    'error' => $e->getMessage(),
                ]);
            }

            $message = 'AI settings have been reset to defaults.';
            if ($queueRestarted) {
                $message .= ' Queue workers will restart automatically to apply changes.';
            } else {
                $message .= ' Please manually restart your queue workers to apply changes.';
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset settings: '.$e->getMessage());
        }
    }

    /**
     * Clear all caches and force refresh settings (useful for development/testing)
     */
    public function clearCaches()
    {
        try {
            // Clear AISetting cache
            AISetting::clearCache();

            // Clear any cached service instances
            app()->forgetInstance(AIService::class);

            Log::info('AI settings caches cleared manually');

            return redirect()->back()->with('success', 'All AI settings caches have been cleared. Settings will be refreshed from database.');

        } catch (\Exception $e) {
            Log::error('Failed to clear AI settings caches', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to clear caches: '.$e->getMessage());
        }
    }
}
