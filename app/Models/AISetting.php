<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AISetting extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_sensitive',
        'is_required',
        'validation_rules',
        'sort_order',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'is_required' => 'boolean',
        'validation_rules' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Cast value based on type
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return match ($this->type) {
                    'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $value,
                    'float' => (float) $value,
                    'array' => json_decode($value, true) ?? [],
                    default => $value,
                };
            },
            set: function ($value) {
                return match ($this->type) {
                    'boolean' => $value ? '1' : '0',
                    'array' => json_encode($value),
                    default => (string) $value,
                };
            }
        );
    }

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "ai_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            // Update existing setting
            $setting->value = $value;
            $setting->save();
        } else {
            // Create new setting (though this shouldn't happen with seeded data)
            $setting = static::create([
                'key' => $key,
                'value' => $value,
                'type' => 'string', // Default type
                'group' => 'general', // Default group
                'label' => $key,
                'description' => '',
                'is_sensitive' => false,
                'is_required' => false,
            ]);
        }

        // Clear cache for this specific key
        Cache::forget("ai_setting_{$key}");

        // Also clear the group cache and global cache
        Cache::forget("ai_settings_group_{$setting->group}");
        Cache::forget('ai_settings_all');
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray(): array
    {
        return Cache::remember('ai_settings_all', 3600, function () {
            return static::all()
                ->keyBy('key')
                ->map(fn ($setting) => $setting->value)
                ->toArray();
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("ai_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("ai_setting_{$key}");
        }
        Cache::forget('ai_settings_all');

        $groups = static::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("ai_settings_group_{$group}");
        }
    }

    /**
     * Get display value (masked for sensitive fields)
     */
    public function getDisplayValueAttribute(): string
    {
        if ($this->is_sensitive && ! empty($this->value)) {
            if (strlen($this->value) <= 8) {
                return str_repeat('*', strlen($this->value));
            }

            return str_repeat('*', 8).substr($this->value, -4);
        }

        return match ($this->type) {
            'boolean' => $this->value ? 'Yes' : 'No',
            'array' => json_encode($this->value),
            default => (string) $this->value,
        };
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
