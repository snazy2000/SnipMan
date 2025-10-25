<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Snippet;
use App\Models\SnippetVersion;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SnippetController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's personal snippets
        $personalSnippets = $user->snippets()
            ->with(['folder', 'creator'])
            ->latest()
            ->paginate(10);

        // Get team snippets from user's teams
        $teamSnippets = collect();
        foreach ($user->teams as $team) {
            $snippets = $team->snippets()
                ->with(['folder', 'creator'])
                ->latest()
                ->get();
            $teamSnippets = $teamSnippets->merge($snippets);
        }

        return view('snippets.index', compact('personalSnippets', 'teamSnippets'));
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

        return view('snippets.create', compact('teams', 'folders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'language' => 'required|string|max:50',
            'content' => 'required|string',
            'folder_id' => 'required|exists:folders,id',
            'owner_type' => 'required|in:personal,team',
            'team_id' => 'nullable|required_if:owner_type,team|exists:teams,id',
        ]);

        // Set owner based on owner_type
        if ($request->owner_type === 'team') {
            $team = Team::findOrFail($request->team_id);
            $this->authorize('update', $team);

            $snippetData = [
                'title' => $request->title,
                'language' => $request->language,
                'content' => $request->content,
                'folder_id' => $request->folder_id,
                'owner_type' => 'App\Models\Team',
                'owner_id' => $team->id,
                'created_by' => Auth::id(),
            ];
        } else {
            $snippetData = [
                'title' => $request->title,
                'language' => $request->language,
                'content' => $request->content,
                'folder_id' => $request->folder_id,
                'owner_type' => 'App\Models\User',
                'owner_id' => Auth::id(),
                'created_by' => Auth::id(),
            ];
        }

        $snippet = Snippet::create($snippetData);

        // Create initial version
        SnippetVersion::create([
            'snippet_id' => $snippet->id,
            'version_number' => 1,
            'content' => $request->content,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('snippets.show', $snippet)
            ->with('success', 'Snippet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Snippet $snippet)
    {
        $this->authorize('view', $snippet);

        $snippet->load(['folder', 'creator', 'versions.creator', 'shares']);

        return view('snippets.show', compact('snippet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        $user = Auth::user();
        $folders = $user->folders()->get();

        // Add team folders where user has editor+ role
        foreach ($user->teams as $team) {
            if (in_array($team->pivot->role, ['owner', 'editor'])) {
                $folders = $folders->merge($team->folders);
            }
        }

        return view('snippets.edit', compact('snippet', 'folders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        $request->validate([
            'title' => 'required|string|max:255',
            'language' => 'required|string|max:50',
            'content' => 'required|string',
            'folder_id' => 'required|exists:folders,id',
        ]);

        // Check if content changed to create new version
        $contentChanged = $snippet->content !== $request->content;

        $snippet->update($request->only(['title', 'language', 'content', 'folder_id']));

        // Create new version if content changed
        if ($contentChanged) {
            $latestVersion = $snippet->versions()->max('version_number') ?? 0;
            SnippetVersion::create([
                'snippet_id' => $snippet->id,
                'version_number' => $latestVersion + 1,
                'content' => $request->content,
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()->route('snippets.show', $snippet)
            ->with('success', 'Snippet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Snippet $snippet)
    {
        $this->authorize('delete', $snippet);

        $snippet->delete();

        return redirect()->route('snippets.index')
            ->with('success', 'Snippet deleted successfully.');
    }
}
