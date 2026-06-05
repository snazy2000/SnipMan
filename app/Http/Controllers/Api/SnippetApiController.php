<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSnippetAI;
use App\Models\AISetting;
use App\Models\Folder;
use App\Models\Snippet;
use App\Models\SnippetVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class SnippetApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $request->input('q', '');
        $ownerFilter = $request->input('owner', 'all');
        $lowerQuery = strtolower($query);

        $results = collect();

        if ($ownerFilter === 'all' || $ownerFilter === 'personal') {
            $personalQuery = $user->snippets()->with(['folder:id,name', 'creator:id,name']);
            if ($query) {
                $personalQuery->where(function ($q) use ($lowerQuery) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$lowerQuery}%"])
                        ->orWhereRaw('LOWER(language) LIKE ?', ["%{$lowerQuery}%"]);
                });
            }
            foreach ($personalQuery->latest()->get() as $s) {
                $results->push($this->formatSnippet($s, null));
            }
        }

        if ($ownerFilter === 'all' || str_starts_with($ownerFilter, 'team:')) {
            $specificTeamId = str_starts_with($ownerFilter, 'team:') ? (int) substr($ownerFilter, 5) : null;
            $teams = $user->teams()->get()->keyBy('id');
            $teamIds = $specificTeamId ? collect([$specificTeamId]) : $teams->keys();

            $teamQuery = Snippet::whereIn('owner_id', $teamIds)
                ->where('owner_type', Team::class)
                ->with(['folder:id,name', 'creator:id,name'])
                ->latest();

            if ($query) {
                $teamQuery->where(function ($q) use ($lowerQuery) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$lowerQuery}%"])
                        ->orWhereRaw('LOWER(language) LIKE ?', ["%{$lowerQuery}%"]);
                });
            }

            foreach ($teamQuery->get() as $s) {
                $results->push($this->formatSnippet($s, $teams->get($s->owner_id)));
            }
        }

        return response()->json([
            'data' => $results->values(),
            'total' => $results->count(),
        ]);
    }

    public function show(Request $request, Snippet $snippet)
    {
        $user = $request->user();

        if (! $this->canView($user, $snippet)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $snippet->load(['folder:id,name', 'creator:id,name']);

        return response()->json($this->formatSnippet($snippet, null, true));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'language' => 'required|string|max:50',
            'content' => 'required|string',
            'folder_id' => 'nullable|exists:folders,id',
            'owner_type' => 'required|in:personal,team',
            'team_id' => 'nullable|required_if:owner_type,team|exists:teams,id',
            'user_tags' => 'nullable|array',
            'user_tags.*' => 'string|max:50',
        ]);

        $user = $request->user();

        if ($validated['owner_type'] === 'team') {
            $team = Team::findOrFail($validated['team_id']);
            $membership = $team->members()->where('user_id', $user->id)->first();
            if (! $membership || ! in_array($membership->pivot->role, ['owner', 'editor'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $ownerType = Team::class;
            $ownerId = $team->id;
        } else {
            $ownerType = User::class;
            $ownerId = $user->id;
        }

        if (! empty($validated['folder_id'])) {
            $folder = Folder::findOrFail($validated['folder_id']);
            $folderOwnerMatches = $folder->owner_type === $ownerType && $folder->owner_id === $ownerId;
            if (! $folderOwnerMatches) {
                return response()->json(['message' => 'Folder does not belong to the selected owner'], 403);
            }
        }

        $snippet = Snippet::create([
            'title' => $validated['title'],
            'language' => $validated['language'],
            'content' => $validated['content'],
            'folder_id' => $validated['folder_id'] ?? null,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'created_by' => $user->id,
            'user_tags' => $validated['user_tags'] ?? [],
        ]);

        SnippetVersion::create([
            'snippet_id' => $snippet->id,
            'version_number' => 1,
            'content' => $validated['content'],
            'created_by' => $user->id,
        ]);

        if (AISetting::get('ai.features.auto_description', false)) {
            ProcessSnippetAI::dispatch($snippet);
        }

        return response()->json($this->formatSnippet($snippet, null, true), 201);
    }

    public function folders(Request $request)
    {
        $user = $request->user();

        $personal = $user->folders()->select('id', 'name', 'parent_id')->get()
            ->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'parent_id' => $f->parent_id, 'owner' => 'personal']);

        $teams = $user->teams()->get()->keyBy('id');
        $teamFolders = Folder::whereIn('owner_id', $teams->keys())
            ->where('owner_type', Team::class)
            ->select('id', 'name', 'parent_id', 'owner_id')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'parent_id' => $f->parent_id,
                'owner' => 'team',
                'team_id' => $f->owner_id,
                'team_name' => $teams->get($f->owner_id)?->name ?? '',
            ]);

        return response()->json(['data' => $personal->merge($teamFolders)->values()]);
    }

    public function teams(Request $request)
    {
        $user = $request->user();
        $teams = $user->teams()->select('teams.id', 'teams.name')
            ->withPivot('role')
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'role' => $t->pivot->role]);

        return response()->json(['data' => $teams]);
    }

    private function canView($user, Snippet $snippet): bool
    {
        if ($snippet->owner_type === User::class) {
            return $snippet->owner_id === $user->id;
        }

        return $user->teams->contains('id', $snippet->owner_id);
    }

    private function formatSnippet(Snippet $snippet, ?Team $team, bool $includeContent = false): array
    {
        $data = [
            'id' => $snippet->id,
            'title' => $snippet->title,
            'language' => $snippet->language,
            'folder' => $snippet->folder ? ['id' => $snippet->folder->id, 'name' => $snippet->folder->name] : null,
            'creator' => $snippet->creator ? ['id' => $snippet->creator->id, 'name' => $snippet->creator->name] : null,
            'owner_type' => $snippet->owner_type === User::class ? 'personal' : 'team',
            'team' => $team ? ['id' => $team->id, 'name' => $team->name] : null,
            'description' => $snippet->description,
            'user_tags' => $snippet->user_tags ?? [],
            'ai_description' => $snippet->ai_description,
            'is_pinned' => (bool) $snippet->is_pinned,
            'created_at' => $snippet->created_at->toISOString(),
            'updated_at' => $snippet->updated_at->toISOString(),
        ];

        if ($includeContent) {
            $data['content'] = $snippet->content;
        }

        return $data;
    }
}
