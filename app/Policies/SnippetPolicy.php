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
        // Super admins can view any snippet
        if ($user->is_super_admin ?? false) {
            return true;
        }

        // User can view if they own it personally
        if ($snippet->owner_type === 'App\Models\User' && $snippet->owner_id === $user->id) {
            return true;
        }

        // User can view if it belongs to a team they're a member of
        if ($snippet->owner_type === 'App\Models\Team') {
            $team = $snippet->owner;
            if (!$team) {
                return false;
            }
            return $team->members->contains($user);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $folder = null): bool
    {
        // Super admins can always create
        if ($user->is_super_admin ?? false) {
            return true;
        }

        // If no folder specified, user can at least try to create (will be validated later)
        if (!$folder) {
            return true;
        }

        // If folder is provided, check if user can create in it
        if ($folder->owner_type === 'App\Models\User' && $folder->owner_id === $user->id) {
            return true;
        }

        // Check if user can create in team folder (must be owner or editor)
        if ($folder->owner_type === 'App\Models\Team') {
            $team = $folder->owner;
            if (!$team) {
                return false;
            }

            // Check if members are loaded, if so use the collection
            if ($team->relationLoaded('members')) {
                $member = $team->members->firstWhere('id', $user->id);
                if (!$member) {
                    return false;
                }
                $role = $member->pivot->role ?? null;
            } else {
                // Fall back to query if not loaded
                $membership = $team->members()->where('user_id', $user->id)->first();
                if (!$membership) {
                    return false;
                }
                $role = $membership->pivot->role ?? null;
            }

            return $role && in_array($role, ['owner', 'editor']);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Snippet $snippet): bool
    {
        // Super admins can update any snippet
        if ($user->is_super_admin ?? false) {
            return true;
        }

        // User can update if they own it personally
        if ($snippet->owner_type === 'App\Models\User' && $snippet->owner_id === $user->id) {
            return true;
        }

        // User can update if it belongs to a team and they have editor+ role
        if ($snippet->owner_type === 'App\Models\Team') {
            $team = $snippet->owner;
            if (!$team) {
                return false;
            }

            // Check if members are loaded, if so use the collection
            if ($team->relationLoaded('members')) {
                $member = $team->members->firstWhere('id', $user->id);
                if (!$member) {
                    return false;
                }
                $role = $member->pivot->role ?? null;
            } else {
                // Fall back to query if not loaded
                $membership = $team->members()->where('user_id', $user->id)->first();
                if (!$membership) {
                    return false;
                }
                $role = $membership->pivot->role ?? null;
            }

            return $role && in_array($role, ['owner', 'editor']);
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

    /**
     * Determine whether the user can share the snippet publicly.
     */
    public function share(User $user, Snippet $snippet): bool
    {
        // Super admins can share any snippet
        if ($user->is_super_admin ?? false) {
            return true;
        }

        // User can share if they own it personally
        if ($snippet->owner_type === 'App\Models\User' && $snippet->owner_id === $user->id) {
            return true;
        }

        // User can share team snippets only if they have editor+ role
        if ($snippet->owner_type === 'App\Models\Team') {
            $team = $snippet->owner;
            if (!$team) {
                return false;
            }

            // Check if members are loaded, if so use the collection
            if ($team->relationLoaded('members')) {
                $member = $team->members->firstWhere('id', $user->id);
                if (!$member) {
                    return false;
                }
                $role = $member->pivot->role ?? null;
            } else {
                // Fall back to query if not loaded
                $membership = $team->members()->where('user_id', $user->id)->first();
                if (!$membership) {
                    return false;
                }
                $role = $membership->pivot->role ?? null;
            }

            return $role && in_array($role, ['owner', 'editor']);
        }

        return false;
    }
}
