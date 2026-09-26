<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property Carbon|null $invitation_expires_at
 * @property Carbon|null $invitation_accepted_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'monaco_theme',
        'monaco_language',
        'invitation_token',
        'invitation_accepted_at',
        'invitation_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_disabled' => 'boolean',
            'invitation_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Scope a query to only include active users (not disabled or deleted).
     */
    public function scopeActive($query)
    {
        return $query->where('is_disabled', false)->whereNull('deleted_at');
    }

    /**
     * Check if user is active (not disabled).
     */
    public function isActive(): bool
    {
        return ! $this->is_disabled && ! $this->trashed();
    }

    /**
     * Get all teams owned by this user.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Get all teams this user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role', 'invitation_status', 'invitation_token', 'invited_at')
            ->withTimestamps();
    }

    /**
     * Get all folders owned by this user.
     */
    public function folders(): MorphMany
    {
        return $this->morphMany(Folder::class, 'owner');
    }

    /**
     * Get all snippets owned by this user.
     */
    public function snippets(): MorphMany
    {
        return $this->morphMany(Snippet::class, 'owner');
    }

    /**
     * Get all snippets created by this user.
     */
    public function createdSnippets(): HasMany
    {
        return $this->hasMany(Snippet::class, 'created_by');
    }

    /**
     * Get all snippet versions created by this user.
     */
    public function createdVersions(): HasMany
    {
        return $this->hasMany(SnippetVersion::class, 'created_by');
    }

    /**
     * Has this user's account invitation passed its expiry?
     *
     * A null expiry is treated as valid so invitations issued before the
     * expiry column existed keep working.
     */
    public function invitationHasExpired(): bool
    {
        return $this->invitation_expires_at !== null
            && $this->invitation_expires_at->isPast();
    }

    /**
     * Deactivate every public share
 belonging to this user's personal snippets.
     *
     * Snippet ownership is polymorphic, so there is no foreign key from users to
     * snippets and nothing cascades when a user is (soft) deleted. Without this,
     * a deleted account's /s/{uuid} links keep serving its code indefinitely.
     */
    public function revokePublicShares(): int
    {
        return SnippetShare::whereIn('snippet_id', $this->snippets()->select('snippets.id'))
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
