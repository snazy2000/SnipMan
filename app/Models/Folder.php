<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'owner_id',
        'owner_type',
    ];

    /**
     * Get the owner of the folder (User or Team).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get all child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Get all snippets in this folder.
     */
    public function snippets(): HasMany
    {
        return $this->hasMany(Snippet::class);
    }

    /**
     * Get all descendant folders recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if this folder is a root folder.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }
}
