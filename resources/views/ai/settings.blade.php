@extends('layouts.snippets')

@section('title', 'AI Settings')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <a href="{{ route('admin.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            <i class="fas fa-robot mr-2 text-indigo-600 dark:text-indigo-400"></i>AI Settings
                        </h1>
                    </div>
                    <p class="ml-12 text-gray-600 dark:text-gray-400">Configure AI providers and features for code analysis</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full {{ $isAvailable ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $providers[$currentProvider]['name'] ?? 'Unknown' }} - {{ $isAvailable ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-2">
                        <form method="POST" action="{{ route('admin.ai.settings.clear-caches') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Clear Caches
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.ai.settings.reset') }}" class="inline" onsubmit="return confirm('Reset all AI settings to defaults?')">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.ai.settings.update') }}" class="p-6 space-y-8">
            @csrf

            <!-- General Settings -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General Settings ({{ $generalSettings->count() }} settings)</h2>
                @if($generalSettings->isEmpty())
                    <p class="text-red-500">No general settings found!</p>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($generalSettings as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ $setting->label }}
                                @if($setting->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            @if($setting->type === 'boolean')
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           id="{{ $setting->key }}"
                                           name="{{ $setting->key }}"
                                           value="1"
                                           {{ $setting->value ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="{{ $setting->key }}" class="ml-2 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $setting->description }}
                                    </label>
                                </div>
                            @elseif($setting->key === 'ai.provider')
                                <select id="{{ $setting->key }}" name="{{ $setting->key }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    @foreach($providers as $key => $provider)
                                        <option value="{{ $key }}" {{ $setting->value === $key ? 'selected' : '' }}>
                                            {{ $provider['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $setting->type === 'integer' ? 'number' : ($setting->type === 'float' ? 'number' : 'text') }}"
                                       id="{{ $setting->key }}"
                                       name="{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       @if($setting->type === 'float') step="0.1" @endif
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       {{ $setting->is_required ? 'required' : '' }}>
                            @endif
                            @if($setting->description && $setting->type !== 'boolean')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $setting->description }}</p>
                            @endif
                            @error($setting->key)
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Provider-specific settings tabs -->
            <div x-data="{ activeTab: '{{ $currentProvider }}' }" id="ai-settings-tabs">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button type="button"
                                @click="activeTab = 'ollama'"
                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'ollama', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'ollama' }"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Ollama Settings
                        </button>
                        <button type="button"
                                @click="activeTab = 'openrouter'"
                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'openrouter', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'openrouter' }"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            OpenRouter Settings
                        </button>
                        <button type="button"
                                @click="activeTab = 'features'"
                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'features', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'features' }"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            AI Features
                        </button>
                        <button type="button"
                                @click="activeTab = 'processing'"
                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'processing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'processing' }"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Processing
                        </button>
                    </nav>
                </div>

                <!-- Ollama Settings -->
                <div x-show="activeTab === 'ollama'" class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ollama Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($ollamaSettings as $setting)
                            @include('ai.partials.setting-field', ['setting' => $setting])
                        @endforeach
                    </div>
                </div>

                <!-- OpenRouter Settings -->
                <div x-show="activeTab === 'openrouter'" class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">OpenRouter Configuration</h3>

                    <!-- Rate Limit Information -->
                    @if(isset($providerInfo) && !empty($providerInfo) && $currentProvider === 'openrouter')
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Current Model Information</h4>
                            <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                <p><strong>Current Model:</strong> {{ $providerInfo['current_model'] ?? 'Unknown' }}</p>
                                @if(isset($providerInfo['is_free_model']) && $providerInfo['is_free_model'])
                                    <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                        <p class="text-yellow-800 dark:text-yellow-200 font-medium">⚠️ You're using a free model which may have rate limits</p>
                                        @if(isset($providerInfo['rate_limit_advice']['recommended_actions']) && !empty($providerInfo['rate_limit_advice']['recommended_actions']))
                                            <div class="mt-2">
                                                <p class="text-yellow-800 dark:text-yellow-200 font-medium">Recommendations:</p>
                                                <ul class="list-disc list-inside text-yellow-700 dark:text-yellow-300 mt-1 space-y-1">
                                                    @foreach($providerInfo['rate_limit_advice']['recommended_actions'] as $action)
                                                        <li>{{ $action }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-green-600 dark:text-green-400">✅ You're using a reliable paid model</p>
                                @endif
                                @if(isset($providerInfo['recommended_models']) && !empty($providerInfo['recommended_models']))
                                    <div class="mt-3">
                                        <p class="text-blue-800 dark:text-blue-200 font-medium">Recommended reliable models:</p>
                                        <ul class="list-disc list-inside text-blue-700 dark:text-blue-300 mt-1 space-y-1">
                                            @foreach($providerInfo['recommended_models'] as $modelId => $modelInfo)
                                                <li>
                                                    <strong>{{ $modelInfo['name'] ?? $modelId }}</strong>
                                                    @if(isset($modelInfo['type']) && $modelInfo['type'] === 'paid')
                                                        <span class="text-green-600 dark:text-green-400">(Paid - Reliable)</span>
                                                    @elseif(isset($modelInfo['type']) && $modelInfo['type'] === 'free')
                                                        <span class="text-yellow-600 dark:text-yellow-400">(Free - May be rate-limited)</span>
                                                    @endif
                                                    @if(isset($modelInfo['cost']))
                                                        <span class="text-xs text-gray-500">- {{ ucfirst($modelInfo['cost']) }} cost</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($openrouterSettings as $setting)
                            @include('ai.partials.setting-field', ['setting' => $setting])
                        @endforeach
                    </div>
                </div>                <!-- Features Settings -->
                <div x-show="activeTab === 'features'" class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($featureSettings as $setting)
                            @include('ai.partials.setting-field', ['setting' => $setting])
                        @endforeach
                    </div>
                </div>

                <!-- Processing Settings -->
                <div x-show="activeTab === 'processing'" class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Processing Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($processingSettings as $setting)
                            @include('ai.partials.setting-field', ['setting' => $setting])
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle provider dropdown change to automatically switch tabs
    const providerSelect = document.getElementById('ai.provider');
    const tabsContainer = document.getElementById('ai-settings-tabs');

    if (providerSelect && tabsContainer) {
        providerSelect.addEventListener('change', function() {
            const selectedProvider = this.value;

            // Get the Alpine.js component instance
            const alpineComponent = Alpine.$data(tabsContainer);

            if (alpineComponent) {
                // Update the active tab to match the selected provider
                alpineComponent.activeTab = selectedProvider;
            }
        });
    }
});
</script>
@endsection
