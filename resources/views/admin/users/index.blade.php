@extends('layouts.snippets')

@section('title', 'User Management')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('admin.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        <i class="fas fa-users mr-3 text-indigo-600 dark:text-indigo-400"></i>User Management
                    </h1>
                </div>
                <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    Manage user accounts and permissions
                </p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                <i class="fas fa-user-plus mr-2"></i>
                New User
            </a>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Teams</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Snippets</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->invitation_token)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-600 text-white">
                                        <i class="fas fa-clock mr-1.5"></i>Pending
                                    </span>
                                @elseif($user->invitation_accepted_at)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-600 text-white">
                                        <i class="fas fa-check mr-1.5"></i>Active
                                    </span>
                                @else
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-600 text-white">
                                        <i class="fas fa-check mr-1.5"></i>Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_super_admin)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-purple-600 text-white">
                                        <i class="fas fa-shield-alt mr-1.5"></i>Super Admin
                                    </span>
                                @else
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->teams_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->snippets_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center space-x-2">
                                    @if($user->invitation_token)
                                        <form action="{{ route('admin.users.resendInvitation', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 dark:bg-blue-500 text-white hover:bg-blue-700 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors duration-200">
                                                <i class="fas fa-envelope mr-1.5 text-xs"></i>
                                                <span class="text-xs font-medium">Resend</span>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-indigo-600 dark:bg-indigo-500 text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 rounded-lg shadow-sm transition-colors duration-200">
                                        <i class="fas fa-edit mr-1.5 text-xs"></i>
                                        <span class="text-xs font-medium">Edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 dark:bg-red-500 text-white hover:bg-red-700 dark:hover:bg-red-600 rounded-lg shadow-sm transition-colors duration-200">
                                                <i class="fas fa-trash mr-1.5 text-xs"></i>
                                                <span class="text-xs font-medium">Delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
@endsection
