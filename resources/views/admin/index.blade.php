@extends('layouts.snippets')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                <i class="fas fa-shield-alt mr-3 text-indigo-600 dark:text-indigo-400"></i>Admin Dashboard
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                Manage users, teams, and system settings
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-l-4 border-blue-500 p-6 transition-all duration-200 hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase">Total Users</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $stats['total_users'] }}</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-4 rounded-lg">
                        <i class="fas fa-users text-3xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Total Teams -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-l-4 border-green-500 p-6 transition-all duration-200 hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase">Total Teams</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $stats['total_teams'] }}</p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900/30 p-4 rounded-lg">
                        <i class="fas fa-user-friends text-3xl text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Manage Users -->
            <a href="{{ route('admin.users') }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all duration-200 p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-users text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 dark:text-gray-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 transition-colors duration-200">Manage Users</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    View, edit, and manage user accounts
                </p>
            </a>

            <!-- Manage Teams -->
            <a href="{{ route('admin.teams') }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all duration-200 p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-user-friends text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 dark:text-gray-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 transition-colors duration-200">Manage Teams</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    View, edit, and manage teams
                </p>
            </a>

            <!-- AI Settings -->
            <a href="{{ route('admin.ai.settings') }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all duration-200 p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-lg group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-robot text-2xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 dark:text-gray-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 transition-colors duration-200">AI Settings</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    Configure AI provider and features
                </p>
            </a>
        </div>
    </div>
@endsection
