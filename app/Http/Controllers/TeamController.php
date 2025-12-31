<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Auth::user()->teams()->with('owner')->paginate(10);
        $ownedTeams = Auth::user()->ownedTeams()->paginate(10);

        return view('teams.index', compact('teams', 'ownedTeams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create([
            'name' => $request->name,
            'owner_id' => Auth::id(),
        ]);

        // Add the owner as a team member with owner role
        $team->members()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);

        $team->load(['owner', 'members', 'folders', 'snippets']);

        return view('teams.show', compact('team'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        $this->authorize('manageSettings', $team);

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('manageSettings', $team);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($request->only('name'));

        return redirect()->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    /**
     * Add a member to the team
     */
    public function addMember(Request $request, Team $team)
    {
        $this->authorize('manageMembers', $team);

        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:owner,editor,viewer',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        $isNewUser = false;

        // Check if user is disabled
        if ($user && $user->is_disabled) {
            return back()->withErrors(['email' => 'This user account is disabled and cannot be added to teams.']);
        }

        // Check if already a member
        if ($user && $team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member of the team.']);
        }

        // Generate invitation token
        $token = \Illuminate\Support\Str::random(64);
        $hashedToken = hash('sha256', $token);

        // If user doesn't exist, create a pending user account
        if (! $user) {
            $isNewUser = true;
            $user = \App\Models\User::create([
                'name' => explode('@', $request->email)[0], // Temporary name
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                'invitation_token' => hash('sha256', $token),
            ]);
        }

        // Add user to team with pending status
        $team->members()->attach($user->id, [
            'role' => $request->role,
            'invitation_status' => 'pending',
            'invitation_token' => $hashedToken,
            'invited_at' => now(),
        ]);

        // Send invitation notification
        $user->notify(new \App\Notifications\TeamInvitation($team, $request->role, $token, $isNewUser));

        return back()->with('success', "Invitation sent to {$request->email}.");
    }

    /**
     * Update a team member's role
     */
    public function updateMemberRole(Request $request, Team $team, \App\Models\User $user)
    {
        $this->authorize('manageMembers', $team);

        $request->validate([
            'role' => 'required|in:owner,editor,viewer',
        ]);

        if (! $team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is not a member of this team.']);
        }

        // Prevent changing the role of the actual team owner
        if ($team->owner_id === $user->id) {
            return back()->withErrors(['error' => 'Cannot change the role of the team owner.']);
        }

        // Prevent removing the last owner
        if ($request->role !== 'owner') {
            $ownerCount = $team->members()->wherePivot('role', 'owner')->count();
            $isCurrentUserOwner = $team->members()->where('user_id', $user->id)->wherePivot('role', 'owner')->exists();

            if ($ownerCount <= 1 && $isCurrentUserOwner) {
                return back()->withErrors(['error' => 'Cannot change role: team must have at least one owner.']);
            }
        }

        $team->members()->updateExistingPivot($user->id, ['role' => $request->role]);

        return back()->with('success', "{$user->name}'s role has been updated to {$request->role}.");
    }

    /**
     * Remove a member from the team
     */
    public function removeMember(Team $team, \App\Models\User $user)
    {
        $this->authorize('manageMembers', $team);

        if (! $team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is not a member of this team.']);
        }

        // Prevent removing the actual team owner
        if ($team->owner_id === $user->id) {
            return back()->withErrors(['error' => 'Cannot remove the team owner. Transfer ownership first or delete the team.']);
        }

        // Prevent removing the last owner
        $ownerCount = $team->members()->wherePivot('role', 'owner')->count();
        $isUserOwner = $team->members()->where('user_id', $user->id)->wherePivot('role', 'owner')->exists();

        if ($ownerCount <= 1 && $isUserOwner) {
            return back()->withErrors(['error' => 'Cannot remove: team must have at least one owner.']);
        }

        $team->members()->detach($user->id);

        return back()->with('success', "{$user->name} has been removed from the team.");
    }

    /**
     * Accept team invitation
     */
    public function acceptInvitation($token)
    {
        $hashedToken = hash('sha256', $token);

        // Find the team invitation
        $membership = \DB::table('team_user')
            ->where('invitation_token', $hashedToken)
            ->where('invitation_status', 'pending')
            ->first();

        if (! $membership) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid or has already been used.');
        }

        $user = \App\Models\User::find($membership->user_id);
        $team = Team::find($membership->team_id);

        // If user hasn't accepted their account invitation yet, redirect to account setup
        if ($user->invitation_token) {
            return redirect()->route('invitation.show', ['token' => $token])
                ->with('info', 'Please set up your account first, then you\'ll be added to the team.');
        }

        // Accept the team invitation
        \DB::table('team_user')
            ->where('id', $membership->id)
            ->update([
                'invitation_status' => 'accepted',
                'invitation_token' => null,
                'updated_at' => now(),
            ]);

        \Auth::login($user);

        return redirect()->route('teams.show', $team)->with('success', 'Welcome to '.$team->name.'!');
    }

    /**
     * Resend team invitation
     */
    public function resendInvitation(Team $team, \App\Models\User $user)
    {
        $this->authorize('manageMembers', $team);

        $membership = $team->members()->where('user_id', $user->id)->first();

        if (! $membership || $membership->pivot->invitation_status !== 'pending') {
            return back()->withErrors(['error' => 'No pending invitation found for this user.']);
        }

        // Generate new token
        $token = \Illuminate\Support\Str::random(64);
        $hashedToken = hash('sha256', $token);

        // Update token
        $team->members()->updateExistingPivot($user->id, [
            'invitation_token' => $hashedToken,
            'invited_at' => now(),
        ]);

        // Resend notification
        $isNewUser = $user->invitation_token !== null;
        $user->notify(new \App\Notifications\TeamInvitation($team, $membership->pivot->role, $token, $isNewUser));

        return back()->with('success', 'Invitation resent to '.$user->email);
    }
}
