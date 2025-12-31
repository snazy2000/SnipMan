@extends('layouts.snippets')

@section('title', $team->name)

@section('content')
@if(auth()->user()->is_super_admin && !$team->members->contains(auth()->user()) && $team->owner_id !== auth()->id())
    <div class="mb-6 bg-yellow-500/10 dark:bg-yellow-500/10 border border-yellow-500/50 dark:border-yellow-500/50 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-shield-alt text-yellow-600 dark:text-yellow-400 text-xl mr-3"></i>
            <div>
                <h3 class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">Viewing as Super Admin</h3>
                <p class="text-xs text-yellow-600 dark:text-yellow-400">You are not a member of this team. You have access due to admin privileges.</p>
            </div>
        </div>
    </div>
@endif

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
        <a href="{{ route('teams.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ $team->name }}</h1>
    </div>

    @can('manageSettings', $team)
        <div class="flex space-x-2">
            <a href="{{ route('teams.edit', $team) }}" class="bg-gray-600 dark:bg-gray-500 hover:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit Team
            </a>
            @can('delete', $team)
                <button type="button"
                        @click="$dispatch('open-delete-team-modal')"
                        class="bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Delete Team
                </button>
            @endcan
        </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Team Info -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 border dark:border-gray-700 transition-colors duration-200">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 transition-colors duration-200">Team Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Owner</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $team->owner->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Created</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $team->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Members</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $team->members->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Folders</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $team->folders->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Snippets</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $team->snippets->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Team Members -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700 transition-colors duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">Team Members</h2>
                    @can('manageMembers', $team)
                        <button onclick="toggleAddMemberForm()"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Member
                        </button>
                    @endcan
                </div>

                <!-- Add Member Form (Hidden by default) -->
                @can('manageMembers', $team)
                    <div id="addMemberForm" class="hidden mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                        <form method="POST" action="{{ route('teams.addMember', $team) }}">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-200">Email Address</label>
                                    <input type="email" name="email" id="email" required
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm transition-colors duration-200"
                                           placeholder="user@example.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-200">Role</label>
                                    <select name="role" id="role" required
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm transition-colors duration-200">
                                        <option value="viewer">Viewer - Can view snippets and folders</option>
                                        <option value="editor">Editor - Can create and edit team content</option>
                                        <option value="owner">Owner - Full access including member management</option>
                                    </select>
                                </div>
                                <div class="flex items-center space-x-3 pt-2">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Member
                                    </button>
                                    <button type="button" onclick="toggleAddMemberForm()"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors duration-200">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Role Descriptions -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                            <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1 transition-colors duration-200">
                                <p><span class="font-medium text-gray-800 dark:text-gray-200">Viewer:</span> Can view team snippets and folders</p>
                                <p><span class="font-medium text-gray-800 dark:text-gray-200">Editor:</span> Can create, edit, and delete team content</p>
                                <p><span class="font-medium text-gray-800 dark:text-gray-200">Owner:</span> Full access including team settings and member management</p>
                            </div>
                        </div>
                    </div>
                @endcan

                <div class="space-y-3">
                    @foreach($team->members as $member)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-200">{{ substr($member->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $member->name }}</p>
                                        @if($member->pivot->invitation_status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 transition-colors duration-200">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $member->email }}</p>
                                    @if($member->pivot->invitation_status === 'pending' && $member->pivot->invited_at)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 transition-colors duration-200">
                                            Invited {{ \Carbon\Carbon::parse($member->pivot->invited_at)->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @can('manageMembers', $team)
                                    <!-- Role Dropdown -->
                                    @if($member->id === auth()->id() || $member->id === $team->owner_id)
                                        <!-- Current user or actual team owner cannot have role changed -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200
                                            @if($member->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                                            @elseif($member->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                            {{ ucfirst($member->pivot->role) }}@if($member->id === $team->owner_id) (Team Owner)@elseif($member->id === auth()->id()) (You)@endif
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('teams.updateMemberRole', [$team, $member]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()"
                                                    class="text-xs rounded-full border-0 font-medium cursor-pointer transition-colors duration-200
                                                    @if($member->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                                                    @elseif($member->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                                <option value="viewer" {{ $member->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                <option value="editor" {{ $member->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                                <option value="owner" {{ $member->pivot->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                            </select>
                                        </form>
                                    @endif

                                    <!-- Resend Invitation -->
                                    @if($member->pivot->invitation_status === 'pending')
                                        <form method="POST" action="{{ route('teams.resendInvitation', [$team, $member]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center p-1 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900 rounded transition-colors duration-200" title="Resend Invitation">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Remove Member -->
                                    @if($member->id !== $team->owner_id && ($member->id !== auth()->id() || $team->members()->wherePivot('role', 'owner')->count() > 1))
                                        <form method="POST" action="{{ route('teams.removeMember', [$team, $member]) }}" class="inline"
                                              onsubmit="return confirm('Are you sure you want to remove {{ $member->name }} from the team?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center p-1 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900 rounded transition-colors duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200
                                        @if($member->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                                        @elseif($member->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                        {{ ucfirst($member->pivot->role) }}
                                    </span>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Folders and Snippets -->
    <div class="lg:col-span-3">
        <!-- Folders -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 border dark:border-gray-700 transition-colors duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">Folders</h2>
                    @can('update', $team)
                        <a href="{{ route('folders.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium transition-colors duration-200">
                            Create Folder
                        </a>
                    @endcan
                </div>

                @if($team->folders->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($team->folders as $folder)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700 transition-colors duration-200">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white transition-colors duration-200">
                                    <a href="{{ route('folders.show', $folder) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                        {{ $folder->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">
                                    {{ $folder->snippets->count() }} {{ Str::plural('snippet', $folder->snippets->count()) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400 text-sm transition-colors duration-200">No folders created yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Snippets -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700 transition-colors duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">Recent Snippets</h2>
                    @can('update', $team)
                        <a href="{{ route('snippets.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium transition-colors duration-200">
                            Create Snippet
                        </a>
                    @endcan
                </div>

                @if($team->snippets->count() > 0)
                    <div class="space-y-3">
                        @foreach($team->snippets->take(5) as $snippet)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white transition-colors duration-200">
                                            <a href="{{ route('snippets.show', $snippet) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                                {{ $snippet->title }}
                                            </a>
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                                            {{ $snippet->language }} • Created {{ $snippet->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 transition-colors duration-200">
                                        {{ $snippet->folder ? $snippet->folder->name : 'No folder' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($team->snippets->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('snippets.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium transition-colors duration-200">
                                View all snippets
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-600 dark:text-gray-400 text-sm transition-colors duration-200">No snippets created yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function toggleAddMemberForm() {
    const form = document.getElementById('addMemberForm');
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        document.getElementById('email').focus();
    } else {
        form.classList.add('hidden');
        // Clear form
        document.getElementById('email').value = '';
        document.getElementById('role').value = 'viewer';
    }
}
</script>

<!-- Delete Team Confirmation Modal -->
<div x-data="{
        showModal: false,
        confirmText: '',
        canDelete: false,
        teamName: '{{ $team->name }}',
        memberCount: {{ $team->members->count() }},
        snippetCount: {{ $team->snippets->count() }}
    }"
     @open-delete-team-modal.window="showModal = true; confirmText = ''; canDelete = false;"
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
                <form action="{{ route('teams.destroy', $team) }}" method="POST" class="inline">
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
