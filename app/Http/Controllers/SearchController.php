<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Snippet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $user = Auth::user();
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        // Base scope: snippets the user can see
        $snippetsQuery = Snippet::where(function ($q) use ($user, $userTeamIds) {
            $q->where(function ($sub) use ($user) {
                $sub->where('owner_type', 'App\Models\User')->where('owner_id', $user->id);
            });
            if (! empty($userTeamIds)) {
                $q->orWhere(function ($sub) use ($userTeamIds) {
                    $sub->where('owner_type', 'App\Models\Team')->whereIn('owner_id', $userTeamIds);
                });
            }
        });

        // Text filter
        if (strlen($query) >= 2) {
            $lowerQuery = strtolower($query);
            $snippetsQuery->where(function ($q) use ($lowerQuery) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$lowerQuery}%"])
                    ->orWhereRaw('LOWER(language) LIKE ?', ["%{$lowerQuery}%"])
                    ->orWhereRaw('LOWER(content) LIKE ?', ["%{$lowerQuery}%"]);
            });
        } elseif (empty($query)) {
            // No query at all — return empty unless filters are provided
            $hasFilters = $request->filled('language') || $request->filled('owner')
                || $request->filled('tag') || $request->filled('date_from') || $request->filled('date_to');
            if (! $hasFilters) {
                return response()->json(['snippets' => [], 'folders' => []]);
            }
        } else {
            return response()->json(['snippets' => [], 'folders' => []]);
        }

        // Language filter
        if ($request->filled('language')) {
            $snippetsQuery->whereRaw('LOWER(language) = ?', [strtolower($request->language)]);
        }

        // Owner filter: personal | team | team:{id}
        if ($request->filled('owner')) {
            $owner = $request->owner;
            if ($owner === 'personal') {
                $snippetsQuery->where('owner_type', 'App\Models\User')->where('owner_id', $user->id);
            } elseif ($owner === 'team') {
                $snippetsQuery->where('owner_type', 'App\Models\Team')->whereIn('owner_id', $userTeamIds);
            } elseif (str_starts_with($owner, 'team:')) {
                $teamId = (int) substr($owner, 5);
                $snippetsQuery->where('owner_type', 'App\Models\Team')->where('owner_id', $teamId);
            }
        }

        // Tag filter
        if ($request->filled('tag')) {
            $snippetsQuery->whereJsonContains('user_tags', $request->tag);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $snippetsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $snippetsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $snippets = $snippetsQuery
            ->select(['id', 'title', 'language', 'folder_id', 'owner_id', 'owner_type', 'created_by', 'updated_at', 'is_pinned'])
            ->with(['folder:id,name', 'creator:id,name'])
            ->orderByDesc('is_pinned')
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        $snippetResults = $snippets->map(fn ($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'language' => $s->language,
            'folder' => $s->folder?->name,
            'creator' => $s->creator?->name,
            'updated_at' => $s->updated_at->diffForHumans(),
            'is_pinned' => (bool) $s->is_pinned,
            'url' => route('snippets.show', $s),
        ]);

        // Folder search (only when text query present)
        $folderResults = collect();
        if (strlen($query) >= 2) {
            $lowerQuery = strtolower($query);
            $foldersQuery = Folder::where(function ($q) use ($user, $userTeamIds) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('owner_type', 'App\Models\User')->where('owner_id', $user->id);
                });
                if (! empty($userTeamIds)) {
                    $q->orWhere(function ($sub) use ($userTeamIds) {
                        $sub->where('owner_type', 'App\Models\Team')->whereIn('owner_id', $userTeamIds);
                    });
                }
            });

            $folderResults = $foldersQuery->whereRaw('LOWER(name) LIKE ?', ["%{$lowerQuery}%"])
                ->withCount('snippets')
                ->select(['id', 'name', 'updated_at'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'snippet_count' => $f->snippets_count,
                    'updated_at' => $f->updated_at->diffForHumans(),
                    'url' => route('folders.show', $f),
                ]);
        }

        return response()->json([
            'snippets' => $snippetResults,
            'folders' => $folderResults,
        ]);
    }

    public function tagAutocomplete(Request $request)
    {
        $q = strtolower($request->get('q', ''));
        $user = Auth::user();
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        // Collect all user_tags JSON arrays from accessible snippets
        $rows = Snippet::where(function ($query) use ($user, $userTeamIds) {
            $query->where(function ($sub) use ($user) {
                $sub->where('owner_type', 'App\Models\User')->where('owner_id', $user->id);
            });
            if (! empty($userTeamIds)) {
                $query->orWhere(function ($sub) use ($userTeamIds) {
                    $sub->where('owner_type', 'App\Models\Team')->whereIn('owner_id', $userTeamIds);
                });
            }
        })
            ->whereNotNull('user_tags')
            ->select('user_tags')
            ->get();

        $tags = $rows->flatMap(fn ($row) => $row->user_tags ?? [])
            ->unique()
            ->filter(fn ($tag) => ! $q || str_contains(strtolower($tag), $q))
            ->values()
            ->take(20);

        return response()->json(['tags' => $tags]);
    }
}
