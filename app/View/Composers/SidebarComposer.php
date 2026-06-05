<?php

namespace App\View\Composers;

use App\Models\Folder;
use App\Models\Snippet;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $data = cache()->remember("sidebar.{$user->id}", 300, function () use ($user) {
            // Personal root folders + nested children + snippets (limited columns)
            $personalFolders = $user->folders()
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($q) => $q
                        ->with([
                            'children' => fn ($q2) => $q2
                                ->with(['snippets' => fn ($sq) => $sq->select(['id', 'title', 'language', 'folder_id'])]),
                        ])
                        ->with(['snippets' => fn ($sq) => $sq->select(['id', 'title', 'language', 'folder_id'])]),
                    'snippets' => fn ($q) => $q->select(['id', 'title', 'language', 'folder_id']),
                ])
                ->get();

            // Personal snippets with no folder
            $unfolderedPersonal = $user->snippets()
                ->where('owner_type', 'App\Models\User')
                ->whereNull('folder_id')
                ->select(['id', 'title', 'language'])
                ->latest()
                ->get();

            // Teams with role pivot
            $teams = $user->teams()->withPivot('role')->get();
            $teamIds = $teams->pluck('id')->toArray();

            // ALL team root folders in ONE query, grouped by team
            $allTeamFolders = collect();
            $allUnfolderedTeam = collect();

            if (! empty($teamIds)) {
                $allTeamFolders = Folder::whereIn('owner_id', $teamIds)
                    ->where('owner_type', 'App\Models\Team')
                    ->whereNull('parent_id')
                    ->with([
                        'children' => fn ($q) => $q
                            ->with([
                                'children' => fn ($q2) => $q2
                                    ->with(['snippets' => fn ($sq) => $sq->select(['id', 'title', 'language', 'folder_id'])]),
                            ])
                            ->with(['snippets' => fn ($sq) => $sq->select(['id', 'title', 'language', 'folder_id'])]),
                        'snippets' => fn ($q) => $q->select(['id', 'title', 'language', 'folder_id']),
                    ])
                    ->get()
                    ->groupBy('owner_id');

                $allUnfolderedTeam = Snippet::whereIn('owner_id', $teamIds)
                    ->where('owner_type', 'App\Models\Team')
                    ->whereNull('folder_id')
                    ->select(['id', 'title', 'language', 'owner_id'])
                    ->latest()
                    ->get()
                    ->groupBy('owner_id');
            }

            return compact('personalFolders', 'unfolderedPersonal', 'teams', 'allTeamFolders', 'allUnfolderedTeam');
        });

        $view->with($data);
    }

    public static function forget(int $userId): void
    {
        cache()->forget("sidebar.{$userId}");
    }
}
