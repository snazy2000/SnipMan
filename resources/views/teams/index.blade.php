@extends('layouts.snippets')

@section('title', 'Teams')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-200">Teams</h1>
    <a href="{{ route('teams.create') }}" class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
        Create Team
    </a>
</div>

<!-- Teams You Own -->
<div class="mb-12">
    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6 transition-colors duration-200">Teams You Own</h2>
    @if($ownedTeams->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ownedTeams as $team)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">
                            <a href="{{ route('teams.show', $team) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                {{ $team->name }}
                            </a>
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 transition-colors duration-200">
                            Owner
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 transition-colors duration-200">
                        {{ $team->members->count() }} {{ Str::plural('member', $team->members->count()) }}
                    </p>
                    <div class="flex space-x-3">
                        <a href="{{ route('teams.show', $team) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium transition-colors duration-200">
                            View
                        </a>
                        <a href="{{ route('teams.edit', $team) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium transition-colors duration-200">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $ownedTeams->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-200">You don't own any teams yet.</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4 transition-colors duration-200">Create your first team to start collaborating with others.</p>
            <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors duration-200">
                Create your first team
            </a>
        </div>
    @endif
</div>

<!-- Teams You Belong To -->
<div>
    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6 transition-colors duration-200">Teams You Belong To</h2>
    @if($teams->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($teams as $team)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">
                            <a href="{{ route('teams.show', $team) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                {{ $team->name }}
                            </a>
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200
                            @if($team->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                            @elseif($team->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                            {{ ucfirst($team->pivot->role) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                            Owner: {{ $team->owner->name }}
                        </p>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 transition-colors duration-200">
                        {{ $team->members->count() }} {{ Str::plural('member', $team->members->count()) }}
                    </p>
                    <div class="flex space-x-3">
                        <a href="{{ route('teams.show', $team) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium transition-colors duration-200">
                            View
                        </a>
                        @if($team->pivot->role === 'owner')
                            <a href="{{ route('teams.edit', $team) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium transition-colors duration-200">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $teams->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-200">You're not a member of any teams yet.</h3>
            <p class="text-gray-600 dark:text-gray-400 transition-colors duration-200">Ask a team owner to invite you to their team.</p>
        </div>
    @endif
</div>
@endsection
