<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
    public function users(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_disabled', false)
                        ->whereNull('invitation_token')
                        ->whereNull('deleted_at');
                    break;
                case 'disabled':
                    $query->where('is_disabled', true);
                    break;
                case 'pending':
                    $query->whereNotNull('invitation_token');
                    break;
            }
        }

        // Filter by role
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_super_admin', true);
            } else {
                $query->where('is_super_admin', false);
            }
        }

        // Only load team data when needed (for delete modal)
        // Don't eager load all members/snippets/folders - too expensive
        $users = $query->with(['ownedTeams' => function ($q) {
            $q->withCount(['members', 'snippets', 'folders']);
        }])
            ->withCount(['teams', 'snippets'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required',
                'email',
                'max:255',
                // Only check uniqueness among non-deleted users
                // Soft-deleted users have email set to null, so they won't conflict
                'unique:users,email,NULL,id,deleted_at,NULL',
            ],
            'is_super_admin' => ['boolean'],
        ]);

        // Generate invitation token
        $token = Str::random(64);

        // Create user with temporary password and invitation token
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(32)), // Temporary random password
            'is_super_admin' => $request->has('is_super_admin'),
            'invitation_token' => hash('sha256', $token),
        ]);

        // Send invitation email
        $user->notify(new \App\Notifications\UserInvitation($token));

        return redirect()->route('admin.users')->with('success', 'User created successfully. An invitation email has been sent to '.$user->email);
    }

    /**
     * Show the specified user.
     */
    public function showUser(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
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
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'is_super_admin' => ['boolean'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Prevent users from removing their own super admin privileges
        if ($user->id !== auth()->id()) {
            $user->is_super_admin = $request->has('is_super_admin');
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    /**
     * Resend user invitation
     */
    public function resendUserInvitation(User $user)
    {
        if (! $user->invitation_token) {
            return redirect()->route('admin.users')->with('error', 'This user has already activated their account.');
        }

        // Generate new token
        $token = Str::random(64);
        $user->invitation_token = hash('sha256', $token);
        $user->save();

        // Resend invitation email
        $user->notify(new \App\Notifications\UserInvitation($token));

        return redirect()->route('admin.users')->with('success', 'Invitation resent to '.$user->email);
    }

    /**
     * Remove the specified user.
     */
    public function destroyUser(User $user, Request $request)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        // Handle owned teams - transfer ownership or delete team
        $ownedTeams = $user->ownedTeams;
        if ($ownedTeams->isNotEmpty()) {
            $newOwners = $request->input('team_owners', []);

            foreach ($ownedTeams as $team) {
                if (isset($newOwners[$team->id]) && $newOwners[$team->id]) {
                    // Transfer ownership
                    $newOwner = User::find($newOwners[$team->id]);
                    if ($newOwner) {
                        $team->owner_id = $newOwner->id;
                        $team->save();

                        // Ensure new owner is a member of the team
                        if (! $team->members()->where('user_id', $newOwner->id)->exists()) {
                            $team->members()->attach($newOwner->id, ['role' => 'owner']);
                        } else {
                            $team->members()->updateExistingPivot($newOwner->id, ['role' => 'owner']);
                        }
                    }
                } else {
                    // No new owner specified, delete the team and all its content
                    $team->snippets()->each(function ($snippet) {
                        $snippet->versions()->delete();
                        $snippet->delete();
                    });
                    $team->folders()->delete();
                    $team->members()->detach();
                    $team->delete();
                }
            }
        }

        // Detach from teams
        $user->teams()->detach();

        // Strip sensitive data and mark as disabled
        // Use unique placeholder to avoid unique constraint issues
        $user->email = "deleted_{$user->id}_".time().'@deleted.local';
        $user->password = null;
        $user->remember_token = null;
        $user->invitation_token = null;
        $user->is_disabled = true;
        $user->save();

        // Soft delete the user
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully. All snippets and versions retain original attribution.');
    }

    /**
     * Get team members for a user (for delete modal).
     */
    public function getUserTeamMembers(User $user)
    {
        $teams = $user->ownedTeams()
            ->with(['members' => function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                    ->select('users.id', 'users.name');
            }])
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'members' => $team->members->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                    ]),
                ];
            });

        return response()->json($teams);
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
     * Show team members.
     */
    public function showTeam(Team $team)
    {
        $team->load(['owner', 'members', 'snippets']);

        return view('admin.teams.show', compact('team'));
    }

    /**
     * Show the form for editing a team.
     */
    public function editTeam(Team $team)
    {
        $team->load(['owner', 'members']);
        $allUsers = User::active()->orderBy('name')->get();

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

    /**
     * Disable a user (reversible).
     */
    public function disableUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot disable your own account.');
        }

        $user->is_disabled = true;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User disabled successfully.');
    }

    /**
     * Enable a disabled user.
     */
    public function enableUser(User $user)
    {
        $user->is_disabled = false;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User enabled successfully.');
    }
}
