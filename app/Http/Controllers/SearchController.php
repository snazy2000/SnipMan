<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Search for snippets and folders
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'snippets' => [],
                'folders' => []
            ]);
        }

        $user = Auth::user();

        // Get user's team IDs for filtering
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        // Search snippets
        $snippetsQuery = Snippet::where(function($q) use ($user, $userTeamIds) {
            // User's own snippets
            $q->where(function($subQ) use ($user) {
                $subQ->where('owner_type', 'App\Models\User')
                     ->where('owner_id', $user->id);
            });

            // Team snippets where user is a member
            if (!empty($userTeamIds)) {
                $q->orWhere(function($subQ) use ($userTeamIds) {
                    $subQ->where('owner_type', 'App\Models\Team')
                         ->whereIn('owner_id', $userTeamIds);
                });
            }
        });

        // Apply search filter - search only existing fields (case-insensitive)
        $snippets = $snippetsQuery->where(function($q) use ($query) {
            $q->where('title', 'ILIKE', "%{$query}%")
              ->orWhere('language', 'ILIKE', "%{$query}%")
              ->orWhere('content', 'ILIKE', "%{$query}%");
        })
        ->with(['folder', 'creator'])
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get();

        $snippetResults = $snippets->map(function($snippet) {
            return [
                'id' => $snippet->id,
                'title' => $snippet->title,
                'language' => $snippet->language,
                'folder' => $snippet->folder ? $snippet->folder->name : null,
                'creator' => $snippet->creator->name,
                'updated_at' => $snippet->updated_at->diffForHumans(),
                'url' => route('snippets.show', $snippet)
            ];
        });

        // Search folders
        $foldersQuery = Folder::where(function($q) use ($user, $userTeamIds) {
            // User's own folders
            $q->where(function($subQ) use ($user) {
                $subQ->where('owner_type', 'App\Models\User')
                     ->where('owner_id', $user->id);
            });

            // Team folders where user is a member
            if (!empty($userTeamIds)) {
                $q->orWhere(function($subQ) use ($userTeamIds) {
                    $subQ->where('owner_type', 'App\Models\Team')
                         ->whereIn('owner_id', $userTeamIds);
                });
            }
        });

        $folders = $foldersQuery->where('name', 'ILIKE', "%{$query}%")
        ->with(['snippets'])
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get();

        $folderResults = $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'snippet_count' => $folder->snippets->count(),
                'updated_at' => $folder->updated_at->diffForHumans(),
                'url' => route('folders.show', $folder)
            ];
        });

        return response()->json([
            'snippets' => $snippetResults,
            'folders' => $folderResults
        ]);
    }
}
