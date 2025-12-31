@extends('layouts.snippets')

@section('title', 'Team Management')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('admin.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        <i class="fas fa-user-friends mr-3 text-indigo-600 dark:text-indigo-400"></i>Team Management
                    </h1>
                </div>
                <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    Manage teams and memberships
                </p>
            </div>
        </div>

        <!-- Teams Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Team</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Members</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Snippets</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-200">
                    @forelse($teams as $team)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-friends text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $team->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $team->owner->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $team->owner->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $team->members_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $team->snippets_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $team->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.teams.show', $team) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 dark:bg-blue-500 text-white hover:bg-blue-700 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors duration-200">
                                        <i class="fas fa-users mr-1.5 text-xs"></i>
                                        <span class="text-xs font-medium">Members</span>
                                    </a>
                                    <a href="{{ route('admin.teams.edit', $team) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-indigo-600 dark:bg-indigo-500 text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 rounded-lg shadow-sm transition-colors duration-200">
                                        <i class="fas fa-edit mr-1.5 text-xs"></i>
                                        <span class="text-xs font-medium">Edit</span>
                                    </a>
                                    <button type="button"
                                            @click="$dispatch('open-delete-modal', { teamId: {{ $team->id }}, teamName: {{ json_encode($team->name) }}, memberCount: {{ $team->members_count }}, snippetCount: {{ $team->snippets_count }} })"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 dark:bg-red-500 text-white hover:bg-red-700 dark:hover:bg-red-600 rounded-lg shadow-sm transition-colors duration-200">
                                        <i class="fas fa-trash mr-1.5 text-xs"></i>
                                        <span class="text-xs font-medium">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No teams found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $teams->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{
            showModal: false,
            teamId: null,
            teamName: '',
            memberCount: 0,
            snippetCount: 0,
            confirmText: '',
            canDelete: false
        }"
         @open-delete-modal.window="
            showModal = true;
            teamId = $event.detail.teamId;
            teamName = $event.detail.teamName;
            memberCount = $event.detail.memberCount;
            snippetCount = $event.detail.snippetCount;
            confirmText = '';
            canDelete = false;
         "
         @keydown.escape.window="showModal = false">

        <!-- Modal Overlay -->
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <!-- Modal Content -->
            <div @click.away="showModal = false"
                 class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <!-- Warning Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-2">Delete Team</h3>

                <!-- Warning Message -->
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-800 dark:text-red-200 font-semibold mb-2">⚠️ This is a destructive action that cannot be undone!</p>
                    <p class="text-sm text-red-700 dark:text-red-300 mb-3">Deleting this team will permanently:</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 space-y-1 ml-4">
                        <li>• Delete the team "<span class="font-semibold" x-text="teamName"></span>"</li>
                        <li>• Remove <span x-text="memberCount"></span> member(s) from the team (users will NOT be deleted)</li>
                        <li>• Delete <span x-text="snippetCount"></span> snippet(s) and all associated folders</li>
                        <li>• Remove all team data and history</li>
                    </ul>
                </div>

                <!-- Confirmation Input -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        To confirm, type the team name: <span class="font-bold" x-text="teamName"></span>
                    </label>
                    <input type="text"
                           x-model="confirmText"
                           @input="canDelete = (confirmText === teamName)"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Type team name here">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button @click="showModal = false"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <form :action="'/admin/teams/' + teamId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                :disabled="!canDelete"
                                :class="canDelete ? 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                            Delete Team
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
