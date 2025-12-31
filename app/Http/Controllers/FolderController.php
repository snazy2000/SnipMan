<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function create(Request $request)
    {
        $user = Auth::user();

        // Only get teams where user has editor or owner role (can create folders)
        $teams = $user->teams()->wherePivotIn('role', ['owner', 'editor'])->get();

        // Get personal folders
        $personalFolders = $user->folders()->get();

        // Get team folders where user has editor+ role
        $teamFolders = collect();
        foreach ($teams as $team) {
            $folders = $team->folders()->get();
            foreach ($folders as $folder) {
                $folder->team_name = $team->name;
            }
            $teamFolders = $teamFolders->merge($folders);
        }

        // Get team_id from query parameter if provided
        $preselectedTeamId = $request->query('team_id');

        // Get parent_id from query parameter if provided
        $preselectedParentId = $request->query('parent_id');

        return view('folders.create', compact('teams', 'personalFolders', 'teamFolders', 'preselectedTeamId', 'preselectedParentId'));
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

    /**
     * Move folder to a different parent folder.
     */
    public function move(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $parentId = $request->parent_id;

        // Prevent circular references
        if ($parentId) {
            $parent = Folder::findOrFail($parentId);

            // Check if the target parent is a descendant of the current folder
            if ($this->isDescendant($folder, $parent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move folder to its own descendant.',
                ], 400);
            }

            // Validate user has access to target parent folder
            $this->authorize('update', $parent);
        }

        $folder->update(['parent_id' => $parentId]);

        return response()->json([
            'success' => true,
            'message' => 'Folder moved successfully.',
        ]);
    }

    /**
     * Check if a folder is a descendant of another folder.
     */
    private function isDescendant(Folder $ancestor, Folder $folder): bool
    {
        $current = $folder;

        while ($current->parent) {
            if ($current->parent->id === $ancestor->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
