@extends('layouts.snippets')

@section('title', 'All Snippets')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">All Snippets</h1>
        <p class="text-sm text-gray-500 mt-1">{{ ($personalSnippets->total() ?? 0) + $teamSnippets->count() }} total</p>
    </div>
</div>

@if(($personalSnippets && $personalSnippets->count() > 0) || $teamSnippets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Personal Snippets -->
        @if($personalSnippets)
            @foreach($personalSnippets as $snippet)
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-medium text-gray-900 truncate">{{ $snippet->title }}</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                            {{ ucfirst($snippet->language) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <pre class="text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-hidden" style="max-height: 120px;"><code>{{ Str::limit($snippet->content, 200) }}</code></pre>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>{{ $snippet->created_at->diffForHumans() }}</span>
                        @if($snippet->folder)
                            <span class="text-gray-400">{{ $snippet->folder->name }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Team Snippets -->
        @foreach($teamSnippets as $snippet)
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-medium text-gray-900 truncate">{{ $snippet->title }}</h3>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                        {{ ucfirst($snippet->language) }}
                    </span>
                </div>

                <div class="mb-3">
                    <pre class="text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-hidden" style="max-height: 120px;"><code>{{ Str::limit($snippet->content, 200) }}</code></pre>
                </div>

                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>{{ $snippet->created_at->diffForHumans() }}</span>
                    <span class="text-gray-400">Team: {{ $snippet->owner->name }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($personalSnippets && $personalSnippets->hasPages())
        <div class="mt-6">
            {{ $personalSnippets->links() }}
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-lg">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No snippets yet</h3>
        <p class="text-gray-600 mb-6">Create your first code snippet to get started.</p>
        <a href="{{ route('snippets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create your first snippet
        </a>
    </div>
@endif
@endsection
