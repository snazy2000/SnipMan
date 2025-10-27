@extends('layouts.snippets')

@section('title', $folder->name)

@section('content')
<div class="mb-8">
    <nav class="text-sm breadcrumbs mb-4">
        <a href="{{ route('folders.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">Folders</a>
        @if($folder->parent)
            <span class="text-gray-400 dark:text-gray-500 mx-2 transition-colors duration-200">/</span>
            <a href="{{ route('folders.show', $folder->parent) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">{{ $folder->parent->name }}</a>
        @endif
        <span class="text-gray-400 dark:text-gray-500 mx-2 transition-colors duration-200">/</span>
        <span class="text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->name }}</span>
    </nav>

    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->name }}</h1>
            <div class="flex items-center mt-2 space-x-4 text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">
                <span>
                    @if($folder->owner_type === 'App\Models\Team')
                        Team: {{ $folder->owner->name }}
                    @else
                        Personal Folder
                    @endif
                </span>
                <span>{{ $folder->snippets->count() }} snippets</span>
                @if($folder->children->count() > 0)
                    <span>{{ $folder->children->count() }} subfolders</span>
                @endif
            </div>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('folders.create') }}?parent_id={{ $folder->id }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors duration-200">
                New Subfolder
            </a>
            <a href="{{ route('snippets.create') }}?folder_id={{ $folder->id }}"
               class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                New Snippet
            </a>
            @can('update', $folder)
                <a href="{{ route('folders.edit', $folder) }}"
                   class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors duration-200">
                    Edit
                </a>
            @endcan
        </div>
    </div>
</div>

<!-- Subfolders -->
@if($folder->children->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 transition-colors duration-200">Subfolders</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($folder->children as $subfolder)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md dark:hover:shadow-lg transition-all duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <h3 class="font-medium text-gray-900 dark:text-white transition-colors duration-200">
                                <a href="{{ route('folders.show', $subfolder) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                    {{ $subfolder->name }}
                                </a>
                            </h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        {{ $subfolder->snippets->count() }} snippets
                        @if($subfolder->children->count() > 0)
                            • {{ $subfolder->children->count() }} subfolders
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Snippets -->
<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white transition-colors duration-200">Snippets</h2>
        @if($folder->snippets->count() > 0)
            <span class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $folder->snippets->count() }} total</span>
        @endif
    </div>

    @if($folder->snippets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($folder->snippets as $snippet)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md dark:hover:shadow-lg transition-all duration-200 cursor-pointer"
                     onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-medium text-gray-900 dark:text-white truncate transition-colors duration-200">{{ $snippet->title }}</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 ml-2 transition-colors duration-200">
                            {{ ucfirst($snippet->language) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-hidden transition-colors duration-200" style="max-height: 120px;"><code>{{ Str::limit($snippet->content, 200) }}</code></pre>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        <span>{{ $snippet->created_at->diffForHumans() }}</span>
                        <span>{{ $snippet->creator->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-200">No snippets in this folder</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 transition-colors duration-200">Add your first snippet to get started.</p>
            <a href="{{ route('snippets.create') }}?folder_id={{ $folder->id }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Snippet
            </a>
        </div>
    @endif
</div>
@endsection
