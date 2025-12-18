<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_teams' => Team::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    /**
     * Display a listing of users.
     */
    public function users()
    {
        $users = User::withCount(['teams', 'snippets'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for editing a user.
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'is_super_admin' => ['boolean'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_super_admin = $request->has('is_super_admin');

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        // Delete user's snippets and folders
        $user->snippets()->delete();
        $user->folders()->delete();

        // Detach from teams
        $user->teams()->detach();

        // Delete owned teams
        $user->ownedTeams()->each(function ($team) {
            $team->delete();
        });

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    /**
     * Display a listing of teams.
     */
    public function teams()
    {
        $teams = Team::with('owner')
            ->withCount(['members', 'snippets'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.teams.index', compact('teams'));
    }

    /**
     * Show the form for editing a team.
     */
    public function editTeam(Team $team)
    {
        $team->load(['owner', 'members']);
        $allUsers = User::orderBy('name')->get();

        return view('admin.teams.edit', compact('team', 'allUsers'));
    }

    /**
     * Update the specified team.
     */
    public function updateTeam(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_id' => ['required', 'exists:users,id'],
        ]);

        $team->update($validated);

        return redirect()->route('admin.teams')->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team.
     */
    public function destroyTeam(Team $team)
    {
        // Delete team's snippets and folders
        $team->snippets()->delete();
        $team->folders()->delete();

        // Detach all members
        $team->members()->detach();

        $team->delete();

        return redirect()->route('admin.teams')->with('success', 'Team deleted successfully.');
    }
}
