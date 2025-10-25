<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'owner_id',
    ];

    /**
     * Get the owner of the team.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get all folders owned by this team.
     */
    public function folders(): MorphMany
    {
        return $this->morphMany(Folder::class, 'owner');
    }

    /**
     * Get all snippets owned by this team.
     */
    public function snippets(): MorphMany
    {
        return $this->morphMany(Snippet::class, 'owner');
    }
}
