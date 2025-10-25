<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SnippetShare extends Model
{
    protected $fillable = [
        'snippet_id',
        'uuid',
        'is_active',
        'views',
        'last_viewed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the snippet this share belongs to.
     */
    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }

    /**
     * Increment the view count and update last viewed timestamp.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Get the public URL for this share.
     */
    public function getPublicUrl(): string
    {
        return url("/s/{$this->uuid}");
    }
}
