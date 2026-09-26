<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessSnippetAI;
use App\Models\AISetting;
use App\Models\Folder;
use App\Models\Snippet;
use App\Models\SnippetShare;
use App\Models\SnippetVersion;
use App\Models\Team;
use App\Models\User;
use App\View\Composers\SidebarComposer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SnippetController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's personal snippets — pinned first, then by date
        $personalSnippets = $user->snippets()
            ->with(['folder', 'creator'])
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        // Get team snippets — single query across all teams
        $teamIds = $user->teams()->pluck('teams.id');
        $teamSnippets = Snippet::whereIn('owner_id', $teamIds)
            ->where('owner_type', Team::class)
            ->with(['folder', 'creator'])
            ->latest()
            ->get();

        // Check if AI auto description feature is enabled
        $aiAutoDescriptionEnabled = AISetting::get('ai.features.auto_description', false);

        return view('snippets.index', compact('personalSnippets', 'teamSnippets', 'aiAutoDescriptionEnabled'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Only get teams where user has editor or owner role (can create snippets)
        $teams = $user->teams()->wherePivotIn('role', ['owner', 'editor'])->get();

        // Get personal folders with hierarchy (recursive loading)
        $personalFolders = $user->folders()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('children');
            }, 'snippets'])
            ->get();

        // Get team folders — single query across all teams
        $teamIds = $teams->pluck('id');
        $teamNameMap = $teams->pluck('name', 'id');
        $allTeamFolders = Folder::whereIn('owner_id', $teamIds)
            ->where('owner_type', Team::class)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('children');
            }, 'snippets'])
            ->get();

        $teamFolders = $allTeamFolders->each(function ($folder) use ($teamNameMap) {
            $folder->team_name = $teamNameMap[$folder->owner_id] ?? '';
        });

        // Pre-select owner context when arriving from a folder page
        $preselectedFolderId = null;
        $preselectedOwnerType = 'personal';
        $preselectedTeamId = null;

        if ($request->filled('folder_id')) {
            $folder = Folder::find($request->folder_id);
            if ($folder) {
                $this->authorize('view', $folder);
                $preselectedFolderId = $folder->id;
                if ($folder->owner_type === 'App\\Models\\Team') {
                    $preselectedOwnerType = 'team';
                    $preselectedTeamId = $folder->owner_id;
                }
            }
        }

        return view('snippets.create', compact(
            'teams', 'personalFolders', 'teamFolders',
            'preselectedFolderId', 'preselectedOwnerType', 'preselectedTeamId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'language' => 'required|string|max:50',
            'content' => 'required|string',
            'folder_id' => 'nullable|exists:folders,id',
            'owner_type' => 'required|in:personal,team',
            'team_id' => 'nullable|required_if:owner_type,team|exists:teams,id',
            'user_tags' => 'nullable',
        ]);

        // Validate folder permissions only if a folder is selected
        if ($request->filled('folder_id')) {
            $folder = Folder::findOrFail($request->folder_id);

            // Check if user can create snippets in this folder
            if ($folder->owner_type === 'App\Models\Team') {
                $team = $folder->owner;
                $membership = $team->members()->where('user_id', Auth::id())->first();

                if (! $membership) {
                    return back()->withErrors(['folder_id' => 'You do not have permission to create snippets in this team.'])->withInput();
                }

                if (! in_array($membership->pivot->role, ['owner', 'editor'])) {
                    return back()->withErrors(['folder_id' => 'You do not have permission to create snippets in this team. Only owners and editors can create snippets.'])->withInput();
                }
            } elseif ($folder->owner_type === 'App\Models\User' && $folder->owner_id !== Auth::id()) {
                return back()->withErrors(['folder_id' => 'You do not have permission to create snippets in this folder.'])->withInput();
            }
        }

        // Parse user_tags from JSON string to array
        $userTags = [];
        if ($request->filled('user_tags')) {
            $decoded = json_decode($request->input('user_tags'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $userTags = $decoded;
            }
        }

        // Set owner based on owner_type
        if ($request->owner_type === 'team') {
            $team = Team::findOrFail($request->team_id);
            $this->authorize('update', $team);

            $snippetData = [
                'title' => $request->title,
                'description' => $request->description,
                'language' => $request->language,
                'content' => $request->content,
                'folder_id' => $request->folder_id,
                'owner_type' => 'App\\Models\\Team',
                'owner_id' => $team->id,
                'created_by' => Auth::id(),
                'user_tags' => $userTags,
            ];
        } else {
            $snippetData = [
                'title' => $request->title,
                'description' => $request->description,
                'language' => $request->language,
                'content' => $request->content,
                'folder_id' => $request->folder_id,
                'owner_type' => 'App\\Models\\User',
                'owner_id' => Auth::id(),
                'created_by' => Auth::id(),
                'user_tags' => $userTags,
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

        // Trigger AI analysis in background if auto_description is enabled
        if (AISetting::get('ai.features.auto_description', false)) {
            ProcessSnippetAI::dispatch($snippet);
        }

        SidebarComposer::forget(Auth::id());

        return redirect()->route('snippets.show', $snippet)
            ->with('success', 'Snippet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Snippet $snippet)
    {
        $this->authorize('view', $snippet);

        // Eager load relationships
        $snippet->load(['folder', 'creator', 'versions.creator', 'shares', 'activeShares']);

        // Only load members relationship if the owner is a Team
        if ($snippet->owner_type === 'App\Models\Team') {
            $snippet->load('owner.members');
        } else {
            $snippet->load('owner');
        }

        // Get teams where user can create snippets (for cloning)
        $user = Auth::user();
        $availableTeams = $user->teams()->wherePivotIn('role', ['owner', 'editor'])->get();

        // Get folders for cloning modal
        $cloneFoldersData = [
            'personal' => $user->folders()->select('id', 'name')->get()->toArray(),
            'teams' => [],
        ];

        foreach ($availableTeams as $team) {
            $cloneFoldersData['teams'][$team->id] = $team->folders()->select('id', 'name')->get()->toArray();
        }

        // Check if AI auto description feature is enabled
        $aiAutoDescriptionEnabled = AISetting::get('ai.features.auto_description', false);

        return view('snippets.show', compact('snippet', 'availableTeams', 'cloneFoldersData', 'aiAutoDescriptionEnabled'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Snippet $snippet)
    {
        // Eager load the owner with members for proper authorization (only for teams)
        if ($snippet->owner_type === 'App\Models\Team') {
            $snippet->load('owner.members');
        } else {
            $snippet->load('owner');
        }

        $this->authorize('update', $snippet);

        $user = Auth::user();

        // Get personal folders with hierarchy (recursive loading)
        $personalFolders = $user->folders()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('children');
            }, 'snippets'])
            ->get();

        // Get team folders with hierarchy (recursive loading)
        $teamFolders = collect();
        foreach ($user->teams as $team) {
            if (in_array($team->pivot->role, ['owner', 'editor'])) {
                $folders = $team->folders()
                    ->whereNull('parent_id')
                    ->with(['children' => function ($query) {
                        $query->with('children');
                    }, 'snippets'])
                    ->get();
                foreach ($folders as $folder) {
                    $folder->team_name = $team->name;
                }
                $teamFolders = $teamFolders->merge($folders);
            }
        }

        return view('snippets.edit', compact('snippet', 'personalFolders', 'teamFolders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Snippet $snippet)
    {
        // Eager load the owner with members for proper authorization (only for teams)
        if ($snippet->owner_type === 'App\Models\Team') {
            $snippet->load('owner.members');
        } else {
            $snippet->load('owner');
        }

        $this->authorize('update', $snippet);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'language' => 'required|string|max:50',
            'content' => 'required|string',
            'folder_id' => 'nullable|exists:folders,id',
            'user_tags' => 'nullable',
        ]);

        // Validate folder permissions if folder is being changed and folder_id is provided
        if ($request->filled('folder_id') && $request->folder_id != $snippet->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);

            // Check if user can move to this folder
            if ($folder->owner_type === 'App\\Models\\Team') {
                $team = $folder->owner;
                $membership = $team->members()->where('user_id', Auth::id())->first();

                if (! $membership || ! in_array($membership->pivot->role, ['owner', 'editor'])) {
                    return back()->withErrors(['folder_id' => 'You do not have permission to move snippets to this folder.'])->withInput();
                }
            } elseif ($folder->owner_type === 'App\\Models\\User' && $folder->owner_id !== Auth::id()) {
                return back()->withErrors(['folder_id' => 'You do not have permission to move snippets to this folder.'])->withInput();
            }
        }

        // Parse user_tags from JSON string to array
        $userTags = [];
        if ($request->filled('user_tags')) {
            $decoded = json_decode($request->input('user_tags'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $userTags = $decoded;
            }
        }

        // Check if content changed to create new version
        $contentChanged = $snippet->content !== $request->content;

        $snippet->update(array_merge(
            $request->only(['title', 'description', 'language', 'content', 'folder_id']),
            ['user_tags' => $userTags]
        ));

        // Create new version if content changed
        if ($contentChanged) {
            $latestVersion = $snippet->versions()->max('version_number') ?? 0;
            SnippetVersion::create([
                'snippet_id' => $snippet->id,
                'version_number' => $latestVersion + 1,
                'content' => $request->content,
                'created_by' => Auth::id(),
            ]);

            // Trigger AI analysis for updated content if auto_description is enabled
            if (AISetting::get('ai.features.auto_description', false)) {
                ProcessSnippetAI::dispatch($snippet, true); // Force reprocess
            }
        }

        SidebarComposer::forget(Auth::id());

        return redirect()->route('snippets.show', $snippet)
            ->with('success', 'Snippet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Snippet $snippet)
    {
        // Eager load the owner with members for proper authorization (only for teams)
        if ($snippet->owner_type === 'App\Models\Team') {
            $snippet->load('owner.members');
        } else {
            $snippet->load('owner');
        }

        $this->authorize('delete', $snippet);

        $snippet->delete();
        SidebarComposer::forget(Auth::id());

        return redirect()->route('snippets.index')
            ->with('success', 'Snippet deleted successfully.');
    }

    /**
     * Toggle pin status for a snippet.
     */
    public function togglePin(Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        $snippet->update(['is_pinned' => ! $snippet->is_pinned]);

        SidebarComposer::forget(Auth::id());

        return response()->json([
            'is_pinned' => $snippet->is_pinned,
            'message' => $snippet->is_pinned ? 'Snippet pinned.' : 'Snippet unpinned.',
        ]);
    }

    /**
     * Clone/Fork a snippet
     */
    public function clone(Request $request, Snippet $snippet)
    {
        // Check if user can view the original snippet
        $this->authorize('view', $snippet);

        $request->validate([
            'title' => 'required|string|max:255',
            'owner_type' => 'required|in:personal,team',
            'team_id' => 'nullable|required_if:owner_type,team|exists:teams,id',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $user = Auth::user();

        // Determine owner
        if ($request->owner_type === 'team') {
            $team = Team::findOrFail($request->team_id);

            // Check if user can create snippets in this team
            $membership = $team->members()->where('user_id', $user->id)->first();
            if (! $membership || ! in_array($membership->pivot->role, ['owner', 'editor'])) {
                abort(403, 'You do not have permission to create snippets in this team.');
            }

            $ownerId = $team->id;
            $ownerType = Team::class;
        } else {
            $ownerId = $user->id;
            $ownerType = User::class;
        }

        // Validate folder belongs to the correct owner
        if ($request->filled('folder_id')) {
            $folder = Folder::findOrFail($request->folder_id);
            if ($folder->owner_id != $ownerId || $folder->owner_type != $ownerType) {
                abort(403, 'The selected folder does not belong to the chosen owner.');
            }
        }

        // Create the cloned snippet
        $clonedSnippet = Snippet::create([
            'title' => $request->title,
            'language' => $snippet->language,
            'content' => $snippet->content,
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'folder_id' => $request->folder_id,
            'created_by' => $user->id,
            'user_tags' => $snippet->user_tags, // Copy user tags
        ]);

        // Create initial version
        SnippetVersion::create([
            'snippet_id' => $clonedSnippet->id,
            'version_number' => 1,
            'content' => $snippet->content,
            'created_by' => $user->id,
        ]);

        // Trigger AI analysis in background if auto_description is enabled
        if (AISetting::get('ai.features.auto_description', false)) {
            ProcessSnippetAI::dispatch($clonedSnippet);
        }

        return redirect()->route('snippets.show', $clonedSnippet)
            ->with('success', 'Snippet cloned successfully.');
    }

    /**
     * Move snippet to a different folder.
     */
    public function move(Request $request, Snippet $snippet)
    {
        try {
            $this->authorize('update', $snippet);

            $request->validate([
                'folder_id' => 'nullable|exists:folders,id',
            ]);

            $folderId = $request->folder_id;

            // If moving to a folder, validate user has access to it
            if ($folderId) {
                $folder = Folder::findOrFail($folderId);

                // Check if user can update this folder (i.e., can add snippets to it)
                $this->authorize('update', $folder);
            }

            $snippet->update(['folder_id' => $folderId]);

            return response()->json([
                'success' => true,
                'message' => 'Snippet moved successfully.',
            ]);

        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to move this snippet.',
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid folder specified.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error moving snippet: '.$e->getMessage(), [
                'snippet_id' => $snippet->id,
                'folder_id' => $request->folder_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while moving the snippet. Please try again.',
            ], 500);
        }
    }

    /**
     * Get share status for the snippet
     */
    public function getShareStatus(Snippet $snippet)
    {
        $this->authorize('view', $snippet);

        $share = $snippet->activeShares()->first();

        if ($share) {
            return response()->json([
                'success' => true,
                'shared' => true,
                'share_url' => $share->getPublicUrl(),
                'uuid' => $share->uuid,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'shared' => false,
                'share_url' => null,
                'uuid' => null,
            ]);
        }
    }

    /**
     * Create or get a public share for the snippet
     */
    public function createShare(Snippet $snippet)
    {
        $this->authorize('share', $snippet);

        // Check if an active share already exists
        $share = $snippet->activeShares()->first();

        if (! $share) {
            $share = $snippet->shares()->create([
                'is_active' => true,
                'views' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'share_url' => $share->getPublicUrl(),
            'uuid' => $share->uuid,
        ]);
    }

    /**
     * Toggle share status
     */
    public function toggleShare(Snippet $snippet)
    {
        $this->authorize('share', $snippet);

        $share = $snippet->activeShares()->first();

        if ($share) {
            $share->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'shared' => false,
                'message' => 'Public sharing disabled',
            ]);
        } else {
            $share = $snippet->shares()->create([
                'is_active' => true,
                'views' => 0,
            ]);

            return response()->json([
                'success' => true,
                'shared' => true,
                'share_url' => $share->getPublicUrl(),
                'uuid' => $share->uuid,
            ]);
        }
    }

    /**
     * View a publicly shared snippet
     */
    public function viewShared($uuid)
    {
        // Validate UUID format
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            abort(404, 'Invalid share link');
        }

        try {
            $share = SnippetShare::where('uuid', $uuid)
                ->where('is_active', true)
                ->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'Share not found');
        }

        $snippet = $share->snippet()->with(['creator', 'folder'])->first();

        if (! $snippet) {
            abort(404, 'Snippet not found');
        }

        // Increment view count
        $share->incrementViews();

        return view('snippets.shared', compact('snippet', 'share'));
    }

    /**
     * List all shared snippets for the current user
     */
    public function sharedList()
    {
        $user = auth()->user();

        // Get user's team IDs
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        // Get all snippets owned by the user or their teams that have active shares
        $sharedSnippets = Snippet::where(function ($query) use ($user, $userTeamIds) {
            // User's own snippets
            $query->where(function ($q) use ($user) {
                $q->where('owner_type', 'App\Models\User')
                    ->where('owner_id', $user->id);
            });

            // Team snippets where user is a member
            if (! empty($userTeamIds)) {
                $query->orWhere(function ($q) use ($userTeamIds) {
                    $q->where('owner_type', 'App\Models\Team')
                        ->whereIn('owner_id', $userTeamIds);
                });
            }
        })
            ->whereHas('activeShares')
            ->with(['activeShares', 'folder', 'creator'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('snippets.shared-list', compact('sharedSnippets'));
    }

    /**
     * Revoke sharing for a snippet
     */
    public function revokeShare(Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        $snippet->activeShares()->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Sharing revoked successfully',
        ]);
    }

    /**
     * Manually trigger AI analysis for a snippet
     */
    public function processAI(Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        Log::info('Manual AI processing requested', [
            'snippet_id' => $snippet->id,
            'snippet_title' => $snippet->title,
            'language' => $snippet->language,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'content_length' => strlen($snippet->content),
            'current_ai_status' => [
                'has_ai_description' => ! empty($snippet->ai_description),
                'ai_processed_at' => $snippet->ai_processed_at,
                'ai_processing_failed' => $snippet->ai_processing_failed,
            ],
        ]);

        try {
            // Dispatch AI processing job
            ProcessSnippetAI::dispatch($snippet, true); // Force reprocess

            Log::info('AI processing job dispatched successfully', [
                'snippet_id' => $snippet->id,
                'queue_name' => config('ai.processing.queue', 'default'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AI analysis started. Results will appear shortly.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch AI processing job', [
                'snippet_id' => $snippet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start AI analysis. Please try again.',
            ], 500);
        }
    }

    /**
     * Bulk operations on multiple snippets (delete, move, tag).
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,move,tag',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:snippets,id',
            'folder_id' => 'nullable|exists:folders,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $user = Auth::user();
        $snippets = Snippet::whereIn('id', $request->ids)->get();

        $allowed = $snippets->filter(function ($s) {
            try {
                $this->authorize('update', $s);

                return true;
            } catch (\Exception) {
                return false;
            }
        });

        if ($allowed->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No snippets could be modified.'], 403);
        }

        switch ($request->action) {
            case 'delete':
                $allowed->each(fn ($s) => $s->delete());
                SidebarComposer::forget($user->id);
                break;

            case 'move':
                if ($request->filled('folder_id')) {
                    $folder = Folder::findOrFail($request->folder_id);

                    // Validation proves the folder exists; it does not prove the
                    // caller may write to it. Same check as move() above.
                    try {
                        $this->authorize('update', $folder);
                    } catch (AuthorizationException) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to move snippets to this folder.',
                        ], 403);
                    }

                    $allowed->each(fn ($s) => $s->update(['folder_id' => $folder->id]));
                } else {
                    $allowed->each(fn ($s) => $s->update(['folder_id' => null]));
                }
                SidebarComposer::forget($user->id);
                break;

            case 'tag':
                $tags = array_unique(array_map('strval', $request->tags ?? []));
                $allowed->each(fn ($s) => $s->update([
                    'user_tags' => array_unique(array_merge($s->user_tags ?? [], $tags)),
                ]));
                break;
        }

        return response()->json([
            'success' => true,
            'affected' => $allowed->count(),
            'message' => "{$allowed->count()} snippet(s) updated.",
        ]);
    }

    /**
     * Export snippets as JSON or ZIP.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'nullable|in:json,zip',
            'owner' => 'nullable|in:personal,all',
        ]);

        $format = $request->get('format', 'json');
        $user = Auth::user();

        $query = $user->snippets()->with(['folder:id,name']);

        if ($request->get('owner') === 'all') {
            $teamIds = $user->teams()->pluck('teams.id');
            $teamSnippets = Snippet::whereIn('owner_id', $teamIds)
                ->where('owner_type', Team::class)
                ->with(['folder:id,name'])
                ->get();
        }

        $snippets = $query->get();

        if (isset($teamSnippets)) {
            $snippets = $snippets->merge($teamSnippets);
        }

        $data = $snippets->map(fn ($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'description' => $s->description,
            'language' => $s->language,
            'content' => $s->content,
            'folder' => $s->folder?->name,
            'tags' => $s->user_tags ?? [],
            'is_pinned' => (bool) $s->is_pinned,
            'created_at' => $s->created_at->toISOString(),
            'updated_at' => $s->updated_at->toISOString(),
        ]);

        if ($format === 'zip') {
            $zip = new \ZipArchive;
            $tmpFile = tempnam(sys_get_temp_dir(), 'snippets_').'.zip';
            $zip->open($tmpFile, \ZipArchive::CREATE);

            foreach ($snippets as $snippet) {
                $ext = $this->extensionForLanguage($snippet->language);
                $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $snippet->title).".{$ext}";
                $zip->addFromString($filename, $snippet->content);
            }

            $zip->close();

            return response()->download($tmpFile, 'snippets.zip', [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend();
        }

        $json = json_encode(['snippets' => $data, 'exported_at' => now()->toISOString()], JSON_PRETTY_PRINT);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="snippets.json"',
        ]);
    }

    private function extensionForLanguage(string $language): string
    {
        return match (strtolower($language)) {
            'javascript', 'js' => 'js',
            'typescript', 'ts' => 'ts',
            'python', 'py' => 'py',
            'php' => 'php',
            'java' => 'java',
            'csharp', 'c#' => 'cs',
            'cpp', 'c++' => 'cpp',
            'c' => 'c',
            'go', 'golang' => 'go',
            'rust' => 'rs',
            'ruby' => 'rb',
            'swift' => 'swift',
            'kotlin' => 'kt',
            'html' => 'html',
            'css' => 'css',
            'sql' => 'sql',
            'bash', 'shell', 'sh' => 'sh',
            'powershell' => 'ps1',
            'json' => 'json',
            'yaml', 'yml' => 'yml',
            'xml' => 'xml',
            'markdown', 'md' => 'md',
            default => 'txt',
        };
    }
}
