<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FolderController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's personal folders
        $personalFolders = $user->folders()
            ->whereNull('parent_id')
            ->with(['children', 'snippets'])
            ->get();

        // Get team folders from user's teams
        $teamFolders = collect();
        foreach ($user->teams as $team) {
            $folders = $team->folders()
                ->whereNull('parent_id')
                ->with(['children', 'snippets'])
                ->get();
            $teamFolders = $teamFolders->merge($folders);
        }

        return view('folders.index', compact('personalFolders', 'teamFolders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $teams = $user->teams;
        $folders = $user->folders()->get();

        // Add team folders where user has editor+ role
        foreach ($user->teams as $team) {
            if (in_array($team->pivot->role, ['owner', 'editor'])) {
                $folders = $folders->merge($team->folders);
            }
        }

        return view('folders.create', compact('teams', 'folders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'owner_type' => 'required|in:personal,team',
            'team_id' => 'nullable|required_if:owner_type,team|exists:teams,id',
        ]);

        // Set owner based on owner_type
        if ($request->owner_type === 'team') {
            $team = Team::findOrFail($request->team_id);
            $this->authorize('update', $team);

            $folderData = [
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'owner_type' => 'App\Models\Team',
                'owner_id' => $team->id,
            ];
        } else {
            $folderData = [
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'owner_type' => 'App\Models\User',
                'owner_id' => Auth::id(),
            ];
        }

        Folder::create($folderData);

        return redirect()->route('folders.index')
            ->with('success', 'Folder created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Folder $folder)
    {
        $this->authorize('view', $folder);

        $folder->load(['children', 'snippets.creator', 'parent']);

        return view('folders.show', compact('folder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        $this->authorize('update', $folder);

        $user = Auth::user();
        $teams = $user->teams;
        $folders = $user->folders()->where('id', '!=', $folder->id)->get();

        return view('folders.edit', compact('folder', 'teams', 'folders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $folder->update($request->only(['name', 'parent_id']));

        return redirect()->route('folders.index')
            ->with('success', 'Folder updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        $folder->delete();

        return redirect()->route('folders.index')
            ->with('success', 'Folder deleted successfully.');
    }
}
