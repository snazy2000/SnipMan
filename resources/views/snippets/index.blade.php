@extends('layouts.snippets')

@section('title', 'All Snippets')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">All Snippets</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">{{ ($personalSnippets->total() ?? 0) + $teamSnippets->count() }} total</p>
    </div>
</div>

@if(($personalSnippets && $personalSnippets->count() > 0) || $teamSnippets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Personal Snippets -->
        @if($personalSnippets)
            @foreach($personalSnippets as $snippet)
                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md dark:hover:shadow-gray-900/25 transition-all duration-200 cursor-pointer" onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 truncate transition-colors duration-200">{{ $snippet->title }}</h3>
                        <div class="flex items-center space-x-2 ml-2">
                            @if($snippet->hasAIAnalysis())
                                <span class="inline-flex items-center p-1 rounded-full text-xs bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300" title="AI Analyzed">
                                    <i class="fas fa-robot text-xs"></i>
                                </span>
                            @elseif($snippet->isAIProcessing())
                                <span class="inline-flex items-center p-1 rounded-full text-xs bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300" title="AI Processing">
                                    <i class="fas fa-spinner fa-spin text-xs"></i>
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 transition-colors duration-200">
                                {{ ucfirst($snippet->language) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <pre class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-2 rounded overflow-hidden transition-colors duration-200" style="max-height: 120px;"><code>{{ Str::limit($snippet->content, 200) }}</code></pre>
                    </div>

                    <!-- AI Tags -->
                    @php
                        $aiTags = $snippet->ai_tags;
                        if (is_string($aiTags)) {
                            $decoded = json_decode($aiTags, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $aiTags = $decoded;
                            } else {
                                $aiTags = [];
                            }
                        }
                    @endphp
                    @if($aiTags && count($aiTags) > 0)
                        <div class="mb-3 flex flex-wrap gap-1">
                            @foreach(array_slice($aiTags, 0, 3) as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 transition-colors duration-200">
                                    <i class="fas fa-tag mr-1 text-xxs"></i>
                                    {{ $tag }}
                                </span>
                            @endforeach
                            @if(count($aiTags) > 3)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                    +{{ count($aiTags) - 3 }}
                                </span>
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        <span>{{ $snippet->created_at->diffForHumans() }}</span>
                        @if($snippet->folder)
                            <span class="text-gray-400 dark:text-gray-500 transition-colors duration-200">{{ $snippet->folder->name }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Team Snippets -->
        @foreach($teamSnippets as $snippet)
            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md dark:hover:shadow-gray-900/25 transition-all duration-200 cursor-pointer" onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 truncate transition-colors duration-200">{{ $snippet->title }}</h3>
                    <div class="flex items-center space-x-2 ml-2">
                        @if($snippet->hasAIAnalysis())
                            <span class="inline-flex items-center p-1 rounded-full text-xs bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300" title="AI Analyzed">
                                <i class="fas fa-robot text-xs"></i>
                            </span>
                        @elseif($snippet->isAIProcessing())
                            <span class="inline-flex items-center p-1 rounded-full text-xs bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300" title="AI Processing">
                                <i class="fas fa-spinner fa-spin text-xs"></i>
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 transition-colors duration-200">
                            {{ ucfirst($snippet->language) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <pre class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-2 rounded overflow-hidden transition-colors duration-200" style="max-height: 120px;"><code>{{ Str::limit($snippet->content, 200) }}</code></pre>
                </div>

                <!-- AI Tags -->
                @php
                    $aiTags = $snippet->ai_tags;
                    if (is_string($aiTags)) {
                        $decoded = json_decode($aiTags, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $aiTags = $decoded;
                        } else {
                            $aiTags = [];
                        }
                    }
                @endphp
                @if($aiTags && count($aiTags) > 0)
                    <div class="mb-3 flex flex-wrap gap-1">
                        @foreach(array_slice($aiTags, 0, 3) as $tag)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 transition-colors duration-200">
                                <i class="fas fa-tag mr-1 text-xxs"></i>
                                {{ $tag }}
                            </span>
                        @endforeach
                        @if(count($aiTags) > 3)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                +{{ count($aiTags) - 3 }}
                            </span>
                        @endif
                    </div>
                @endif

                <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                    <span>{{ $snippet->created_at->diffForHumans() }}</span>
                    <span class="text-gray-400 dark:text-gray-500 transition-colors duration-200">Team: {{ $snippet->owner->name }}</span>
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
        <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg transition-colors duration-200">
            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2 transition-colors duration-200">No snippets yet</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6 transition-colors duration-200">Create your first code snippet to get started.</p>
        <a href="{{ route('snippets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create your first snippet
        </a>
    </div>
@endif
@endsection
