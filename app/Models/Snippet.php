<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snippet extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'owner_id',
        'owner_type',
        'title',
        'language',
        'content',
        'created_by',
        'ai_description',
        'ai_processed_at',
        'ai_processing_failed',
        'user_tags',
    ];

    protected $casts = [
        'user_tags' => 'array',
        'ai_processed_at' => 'datetime',
        'ai_processing_failed' => 'boolean',
    ];

    /**
     * Get the owner of the snippet (User or Team).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the folder this snippet belongs to.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user who created this snippet.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all versions of this snippet.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(SnippetVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get the latest version of this snippet.
     */
    public function latestVersion(): HasMany
    {
        return $this->versions()->latest('version_number')->limit(1);
    }

    /**
     * Get all shares for this snippet.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(SnippetShare::class);
    }

    /**
     * Get active shares for this snippet.
     */
    public function activeShares(): HasMany
    {
        return $this->shares()->where('is_active', true);
    }

    /**
     * Check if AI analysis is available for this snippet.
     */
    public function hasAIAnalysis(): bool
    {
        return $this->ai_processed_at && ! $this->ai_processing_failed;
    }

    /**
     * Check if AI analysis is currently being processed.
     */
    public function isAIProcessing(): bool
    {
        return ! $this->ai_processed_at && ! $this->ai_processing_failed;
    }

    /**
     * Check if AI analysis failed.
     */
    public function hasAIProcessingFailed(): bool
    {
        return $this->ai_processing_failed;
    }

    /**
     * Get the AI description or fallback to manual description.
     */
    public function getBestDescription(): ?string
    {
        return $this->ai_description ?: $this->description;
    }

    /**
     * Trigger AI analysis for this snippet.
     */
    public function processAI(bool $forceReprocess = false): void
    {
        \App\Jobs\ProcessSnippetAI::dispatch($this, $forceReprocess);
    }
}
