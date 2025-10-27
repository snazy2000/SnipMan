@extends('layouts.snippets')

@section('title', 'Folder Management')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-200">Folder Management</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors duration-200">Organize and manage your folder structure</p>
    </div>
    <a href="{{ route('folders.create') }}" class="bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
        Create New Folder
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Personal Folders Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-200">Personal Folders</h2>
        @if($personalFolders->count() > 0)
            <div class="space-y-3">
                @foreach($personalFolders as $folder)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">
                                    {{ $folder->snippets->count() }} snippets
                                    @if($folder->children->count() > 0)
                                        • {{ $folder->children->count() }} subfolders
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('folders.show', $folder) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm transition-colors duration-200">View</a>
                            <a href="{{ route('folders.edit', $folder) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm transition-colors duration-200">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 transition-colors duration-200">No personal folders created yet</p>
            </div>
        @endif
    </div>

    <!-- Team Folders Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-200">Team Folders</h2>
        @if($teamFolders->count() > 0)
            <div class="space-y-3">
                @foreach($teamFolders as $folder)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-400 dark:text-blue-500 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">
                                    Team: {{ $folder->owner->name }} • {{ $folder->snippets->count() }} snippets
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('folders.show', $folder) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm transition-colors duration-200">View</a>
                            @can('update', $folder)
                                <a href="{{ route('folders.edit', $folder) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm transition-colors duration-200">Edit</a>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 transition-colors duration-200">No team folders available</p>
            </div>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-colors duration-200">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 transition-colors duration-200">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('folders.create') }}" class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <div>
                <h4 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">Create Folder</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">Add a new folder</p>
            </div>
        </a>

        <a href="{{ route('snippets.create') }}" class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            <div>
                <h4 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">New Snippet</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">Create a code snippet</p>
            </div>
        </a>

        <a href="{{ route('teams.index') }}" class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <div>
                <h4 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">Manage Teams</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">View team settings</p>
            </div>
        </a>
    </div>
</div>
@endsection
