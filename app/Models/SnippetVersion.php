<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnippetVersion extends Model
{
    protected $fillable = [
        'snippet_id',
        'version_number',
        'content',
        'created_by',
    ];

    /**
     * Get the snippet this version belongs to.
     */
    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
