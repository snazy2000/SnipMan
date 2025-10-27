@extends('layouts.snippets')

@section('title', $team->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
        <a href="{{ route('teams.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ $team->name }}</h1>
    </div>

    @can('update', $team)
        <div class="flex space-x-2">
            <a href="{{ route('teams.edit', $team) }}" class="bg-gray-600 dark:bg-gray-500 hover:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit Team
            </a>
            @can('delete', $team)
                <form method="POST" action="{{ route('teams.destroy', $team) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this team?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Delete Team
                    </button>
                </form>
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
                    @can('update', $team)
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
                @can('update', $team)
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
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @can('update', $team)
                                    <!-- Role Dropdown -->
                                    @if($member->id === auth()->id())
                                        <!-- Current user cannot change their own role -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200
                                            @if($member->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                                            @elseif($member->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                            {{ ucfirst($member->pivot->role) }} (You)
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

                                    <!-- Remove Member -->
                                    @if($member->id !== auth()->id() || $team->members()->wherePivot('role', 'owner')->count() > 1)
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
                                        {{ $snippet->folder->name }}
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
@endsection
