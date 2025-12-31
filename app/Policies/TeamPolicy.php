<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view teams
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // Super admins can view any team
        if ($user->is_super_admin) {
            return true;
        }

        // User can view team if they are a member or owner
        return $team->members->contains($user) || $team->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create teams
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Super admins can update any team
        if ($user->is_super_admin) {
            return true;
        }

        // Only team owner or members with owner/editor role can update
        if ($team->owner_id === $user->id) {
            return true;
        }

        $membership = $team->members()->where('user_id', $user->id)->first();
        return $membership && in_array($membership->pivot->role, ['owner', 'editor']);
    }

    /**
     * Determine whether the user can manage team settings and members.
     * Only the team owner can manage settings and members.
     */
    public function manageSettings(User $user, Team $team): bool
    {
        // Super admins can manage any team
        if ($user->is_super_admin) {
            return true;
        }

        // Only team owner or members with owner role can manage settings
        if ($team->owner_id === $user->id) {
            return true;
        }

        $membership = $team->members()->where('user_id', $user->id)->first();
        return $membership && $membership->pivot->role === 'owner';
    }

    /**
     * Determine whether the user can manage team members (add, remove, change roles).
     * Only the team owner can manage members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        return $this->manageSettings($user, $team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Super admins can delete any team
        if ($user->is_super_admin) {
            return true;
        }

        // Only team owner can delete the team
        return $team->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return $this->delete($user, $team);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $this->delete($user, $team);
    }
}
