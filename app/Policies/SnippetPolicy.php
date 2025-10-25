<?php

namespace App\Policies;

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SnippetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Snippet $snippet): bool
    {
        // User can view if they own it personally
        if ($snippet->owner_type === 'App\Models\User' && $snippet->owner_id === $user->id) {
            return true;
        }

        // User can view if it belongs to a team they're a member of
        if ($snippet->owner_type === 'App\Models\Team') {
            $team = $snippet->owner;
            return $team->members->contains($user);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Snippet $snippet): bool
    {
        // User can update if they own it personally
        if ($snippet->owner_type === 'App\Models\User' && $snippet->owner_id === $user->id) {
            return true;
        }

        // User can update if it belongs to a team and they have editor+ role
        if ($snippet->owner_type === 'App\Models\Team') {
            $team = $snippet->owner;
            $membership = $team->members()->where('user_id', $user->id)->first();
            return $membership && in_array($membership->pivot->role, ['owner', 'editor']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Snippet $snippet): bool
    {
        return $this->update($user, $snippet);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Snippet $snippet): bool
    {
        return $this->update($user, $snippet);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Snippet $snippet): bool
    {
        return $this->update($user, $snippet);
    }
}
