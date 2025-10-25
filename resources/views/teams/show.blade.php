@extends('layouts.snippets')

@section('title', $team->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
        <a href="{{ route('teams.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $team->name }}</h1>
    </div>

    @can('update', $team)
        <div class="flex space-x-2">
            <a href="{{ route('teams.edit', $team) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit Team
            </a>
            @can('delete', $team)
                <form method="POST" action="{{ route('teams.destroy', $team) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this team?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Delete Team
                    </button>
                </form>
            @endcan
        </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Team Info -->
    <div class="lg:col-span-1">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Team Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Owner</dt>
                        <dd class="text-sm text-gray-900">{{ $team->owner->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="text-sm text-gray-900">{{ $team->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Members</dt>
                        <dd class="text-sm text-gray-900">{{ $team->members->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Folders</dt>
                        <dd class="text-sm text-gray-900">{{ $team->folders->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Snippets</dt>
                        <dd class="text-sm text-gray-900">{{ $team->snippets->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Team Members -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Team Members</h2>
                <div class="space-y-3">
                    @foreach($team->members as $member)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500">{{ $member->email }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($member->pivot->role === 'owner') bg-indigo-100 text-indigo-800
                                @elseif($member->pivot->role === 'editor') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($member->pivot->role) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Folders and Snippets -->
    <div class="lg:col-span-2">
        <!-- Folders -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Folders</h2>
                    @can('update', $team)
                        <a href="{{ route('folders.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            Create Folder
                        </a>
                    @endcan
                </div>

                @if($team->folders->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($team->folders as $folder)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('folders.show', $folder) }}" class="hover:text-indigo-600">
                                        {{ $folder->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $folder->snippets->count() }} {{ Str::plural('snippet', $folder->snippets->count()) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 text-sm">No folders created yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Snippets -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Recent Snippets</h2>
                    @can('update', $team)
                        <a href="{{ route('snippets.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            Create Snippet
                        </a>
                    @endcan
                </div>

                @if($team->snippets->count() > 0)
                    <div class="space-y-3">
                        @foreach($team->snippets->take(5) as $snippet)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('snippets.show', $snippet) }}" class="hover:text-indigo-600">
                                                {{ $snippet->title }}
                                            </a>
                                        </h3>
                                        <p class="text-xs text-gray-500">
                                            {{ $snippet->language }} • Created {{ $snippet->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ $snippet->folder->name }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($team->snippets->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('snippets.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                View all snippets
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-600 text-sm">No snippets created yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
