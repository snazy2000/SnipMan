@extends('layouts.snippets')

@section('title', $team->name . ' - Members')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('admin.teams') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                    <i class="fas fa-users mr-3 text-indigo-600 dark:text-indigo-400"></i>{{ $team->name }} - Members
                </h1>
            </div>
            <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                View and manage team members and their invitation status
            </p>
        </div>

        <!-- Team Info Cards -->
        <div class="flex gap-4 mb-6 flex-nowrap">
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center transition-colors duration-200">
                <i class="fas fa-crown text-3xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Owner</p>
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 text-center">{{ $team->owner->name }}</p>
            </div>

            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center transition-colors duration-200">
                <i class="fas fa-users text-3xl text-green-600 dark:text-green-400 mb-2"></i>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Total Members</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $team->members->count() }}</p>
            </div>

            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center transition-colors duration-200">
                <i class="fas fa-clock text-3xl text-yellow-600 dark:text-yellow-400 mb-2"></i>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Pending</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $team->members->where('pivot.invitation_status', 'pending')->count() }}</p>
            </div>

            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center transition-colors duration-200">
                <i class="fas fa-code text-3xl text-blue-600 dark:text-blue-400 mb-2"></i>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Snippets</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $team->snippets->count() }}</p>
            </div>
        </div>

        <!-- Members Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Team Members</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Account Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-200">
                    @forelse($team->members as $member)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200
                                    @if($member->pivot->role === 'owner') bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200
                                    @elseif($member->pivot->role === 'editor') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                    @if($member->pivot->role === 'owner')
                                        <i class="fas fa-crown mr-1"></i>
                                    @elseif($member->pivot->role === 'editor')
                                        <i class="fas fa-edit mr-1"></i>
                                    @else
                                        <i class="fas fa-eye mr-1"></i>
                                    @endif
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->pivot->invitation_status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                        <i class="fas fa-clock mr-1"></i> Pending Invitation
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        <i class="fas fa-check mr-1"></i> Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($member->pivot->invitation_status === 'pending' && $member->pivot->invited_at)
                                    <div>Invited {{ \Carbon\Carbon::parse($member->pivot->invited_at)->diffForHumans() }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($member->pivot->invited_at)->format('M d, Y g:i A') }}</div>
                                @else
                                    {{ \Carbon\Carbon::parse($member->pivot->created_at)->format('M d, Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->invitation_token)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                                        <i class="fas fa-user-clock mr-1"></i> Pending Activation
                                    </span>
                                @elseif($member->invitation_accepted_at)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        <i class="fas fa-user-check mr-1"></i> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        <i class="fas fa-user mr-1"></i> Active
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('admin.teams') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Teams
            </a>
            <div class="flex space-x-3">
                <a href="{{ route('teams.show', $team) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-blue-500 transition-colors duration-200">
                    <i class="fas fa-eye mr-2"></i>
                    View Team
                </a>
                <a href="{{ route('admin.teams.edit', $team) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Team
                </a>
            </div>
        </div>
    </div>
@endsection
