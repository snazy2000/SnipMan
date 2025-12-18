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
    @elseif($setting->type === 'select')
        <select id="{{ $setting->key }}"
                name="{{ $setting->key }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                {{ $setting->is_required ? 'required' : '' }}>
            @foreach(json_decode($setting->allowed_values, true) as $value => $label)
                <option value="{{ $value }}" {{ $setting->value === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    @elseif($setting->type === 'text' && str_contains($setting->key, 'api_key'))
        <div class="relative">
            <input type="password"
                   id="{{ $setting->key }}"
                   name="{{ $setting->key }}"
                   value="{{ $setting->display_value }}"
                   class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                   {{ $setting->is_required ? 'required' : '' }}
                   placeholder="{{ $setting->is_required ? 'Required' : 'Optional' }}">
            <button type="button"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5"
                    onclick="togglePassword('{{ $setting->key }}')">
                <span id="{{ $setting->key }}_toggle" class="text-gray-500 dark:text-gray-400">👁️</span>
            </button>
        </div>
    @else
        <input type="{{ $setting->type === 'integer' ? 'number' : ($setting->type === 'float' ? 'number' : 'text') }}"
               id="{{ $setting->key }}"
               name="{{ $setting->key }}"
               value="{{ $setting->value }}"
               @if($setting->type === 'float') step="0.1" @endif
               @if($setting->type === 'integer') step="1" @endif
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
               {{ $setting->is_required ? 'required' : '' }}
               placeholder="{{ $setting->is_required ? 'Required' : 'Optional' }}">
    @endif

    @if($setting->description && $setting->type !== 'boolean')
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $setting->description }}</p>
    @endif

    @error($setting->key)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '_toggle');

    if (field.type === 'password') {
        field.type = 'text';
        toggle.textContent = '🙈';
    } else {
        field.type = 'password';
        toggle.textContent = '👁️';
    }
}
</script>