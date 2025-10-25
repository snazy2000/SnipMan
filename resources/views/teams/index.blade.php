@extends('layouts.snippets')

@section('title', 'Teams')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-semibold text-gray-900">Teams</h1>
    <a href="{{ route('teams.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        Create Team
    </a>
</div>

<!-- Teams You Own -->
<div class="mb-12">
    <h2 class="text-lg font-medium text-gray-900 mb-6">Teams You Own</h2>
    @if($ownedTeams->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ownedTeams as $team)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <a href="{{ route('teams.show', $team) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $team->name }}
                            </a>
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            Owner
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ $team->members->count() }} {{ Str::plural('member', $team->members->count()) }}
                    </p>
                    <div class="flex space-x-3">
                        <a href="{{ route('teams.show', $team) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
                            View
                        </a>
                        <a href="{{ route('teams.edit', $team) }}" class="text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors">
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
        <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">You don't own any teams yet.</h3>
            <p class="text-gray-600 mb-4">Create your first team to start collaborating with others.</p>
            <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                Create your first team
            </a>
        </div>
    @endif
</div>

<!-- Teams You Belong To -->
<div>
    <h2 class="text-lg font-medium text-gray-900 mb-6">Teams You Belong To</h2>
    @if($teams->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($teams as $team)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <a href="{{ route('teams.show', $team) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $team->name }}
                            </a>
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($team->pivot->role === 'owner') bg-indigo-100 text-indigo-800
                            @elseif($team->pivot->role === 'editor') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($team->pivot->role) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <p class="text-sm text-gray-600">
                            Owner: {{ $team->owner->name }}
                        </p>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ $team->members->count() }} {{ Str::plural('member', $team->members->count()) }}
                    </p>
                    <div class="flex space-x-3">
                        <a href="{{ route('teams.show', $team) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
                            View
                        </a>
                        @if($team->pivot->role === 'owner')
                            <a href="{{ route('teams.edit', $team) }}" class="text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors">
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
        <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">You're not a member of any teams yet.</h3>
            <p class="text-gray-600">Ask a team owner to invite you to their team.</p>
        </div>
    @endif
</div>
@endsection
