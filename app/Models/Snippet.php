<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snippet extends Model
{
    protected $fillable = [
        'folder_id',
        'owner_id',
        'owner_type',
        'title',
        'language',
        'content',
        'created_by',
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
}
