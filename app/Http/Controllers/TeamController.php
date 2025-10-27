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
        $this->authorize('update', $team);

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

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
        $this->authorize('update', $team);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:owner,editor,viewer'
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found with this email address.']);
        }

        if ($team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member of the team.']);
        }

        $team->members()->attach($user->id, ['role' => $request->role]);

        return back()->with('success', "{$user->name} has been added to the team as {$request->role}.");
    }

    /**
     * Update a team member's role
     */
    public function updateMemberRole(Request $request, Team $team, \App\Models\User $user)
    {
        $this->authorize('update', $team);

        $request->validate([
            'role' => 'required|in:owner,editor,viewer'
        ]);

        if (!$team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is not a member of this team.']);
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
        $this->authorize('update', $team);

        if (!$team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is not a member of this team.']);
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
}
