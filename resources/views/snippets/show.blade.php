@extends('layouts.snippets')

@section('title', $snippet->title)

@push('styles')
    <style>
        .code-container {
            position: relative;
        }

        /* History sidebar overlay */
        #history-sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }

        #history-sidebar:not(.hidden) {
            transform: translateX(0);
        }

        /* Backdrop overlay */
        .history-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <nav class="text-sm breadcrumbs mb-4">
            <a href="{{ route('snippets.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">Snippets</a>
            @if($snippet->folder)
                <span class="text-gray-400 dark:text-gray-500 mx-2 transition-colors duration-200">/</span>
                <a href="{{ route('folders.show', $snippet->folder) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">{{ $snippet->folder->name }}</a>
            @endif
            <span class="text-gray-400 dark:text-gray-500 mx-2 transition-colors duration-200">/</span>
            <span class="text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->title }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200 break-words">{{ $snippet->title }}</h1>
                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center mt-3 gap-2 sm:gap-x-4 text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 transition-colors duration-200 w-fit">
                        {{ ucfirst($snippet->language) }}
                    </span>
                    <span class="inline-flex items-center">
                        @if($snippet->owner_type === 'App\Models\Team')
                            <i class="fas fa-users mr-1.5 text-xs"></i> {{ $snippet->owner->name }}
                        @else
                            <i class="fas fa-user mr-1.5 text-xs"></i> Personal
                        @endif
                    </span>
                    @if($snippet->folder)
                        <span class="inline-flex items-center"><i class="fas fa-folder mr-1.5 text-xs"></i> {{ $snippet->folder->name }}</span>
                    @endif
                    <span class="inline-flex items-center"><i class="fas fa-user-circle mr-1.5 text-xs"></i> {{ $snippet->creator->name }}</span>
                    <span class="inline-flex items-center"><i class="fas fa-clock mr-1.5 text-xs"></i> {{ $snippet->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 lg:flex-nowrap flex-shrink-0">
                @can('update', $snippet)
                    <a href="{{ route('snippets.edit', $snippet) }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap">
                        <i class="fas fa-edit mr-1.5"></i>
                        <span>Edit</span>
                    </a>
                @endcan

                <button onclick="copyToClipboard()"
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap">
                    <i class="fas fa-copy mr-1.5"></i>
                    <span>Copy</span>
                </button>

                <!-- More Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="inline-flex items-center justify-center min-w-[2.5rem] px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-600">
                        <div class="py-1">
                            @can('share', $snippet)
                            <button onclick="toggleShare(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <i class="fas fa-share mr-2"></i>
                                Share
                            </button>
                            @endcan

                            <button onclick="toggleHistory(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <i class="fas fa-history mr-2"></i>
                                Version History
                            </button>

                            <button onclick="downloadSnippet(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Download
                            </button>

                            <button onclick="openCloneModal(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <i class="fas fa-clone mr-2"></i>
                                Clone Snippet
                            </button>

                            @can('delete', $snippet)
                                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                                <button onclick="confirmDelete(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 transition-colors duration-200">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Display -->
    <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden mb-8 transition-colors duration-200">
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center transition-colors duration-200">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->title }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ ucfirst($snippet->language) }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ strlen($snippet->content) }} characters</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ substr_count($snippet->content, "\n") + 1 }} lines</span>
            </div>
        </div>
        <div class="code-container">
            <!-- Monaco Editor Container (Read-only) -->
            <div id="monaco-editor" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Snippet Info -->
    <div class="mb-8">
        <!-- Details -->
        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-6 transition-colors duration-200">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200">Snippet Details</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Language</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ ucfirst($snippet->language) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Owner</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            @if($snippet->owner_type === 'App\Models\Team')
                                {{ $snippet->owner->name }} (Team)
                            @else
                                Personal
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Folder</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            @if($snippet->folder)
                                <a href="{{ route('folders.show', $snippet->folder) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">
                                    {{ $snippet->folder->name }}
                                </a>
                            @else
                                No folder
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Created by</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->created_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Last updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->updated_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 transition-colors duration-200">Versions</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            @php
                                $latestVersionNumber = $snippet->versions->max('version_number') ?? 0;
                                $historicalCount = $snippet->versions->where('version_number', '<', $latestVersionNumber)->count();
                            @endphp
                            Current
                            @if($historicalCount > 0)
                                + {{ $historicalCount }} historical
                            @else
                                (no history)
                            @endif
                        </dd>
                    </div>
                </dl>

                <!-- AI Analysis Section -->
                @if(($snippet->hasAIAnalysis() || ($snippet->isAIProcessing() && $aiAutoDescriptionEnabled) || $snippet->hasAIProcessingFailed()) && $aiAutoDescriptionEnabled)
                    <div class="mt-8 pt-6 border-t-2 border-gradient-to-r from-indigo-200 to-purple-200 dark:from-indigo-700 dark:to-purple-700">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                <i class="fas fa-robot mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                AI Analysis
                            </h4>
                            @can('update', $snippet)
                                <button onclick="regenerateAI()" id="regenerate-btn"
                                        class="flex items-center px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all duration-200">
                                    <i class="fas fa-refresh mr-2"></i>
                                    Regenerate Analysis
                                </button>
                            @endcan
                        </div>

                        @if($snippet->hasAIAnalysis())

                            <div class="space-y-6">
                                <!-- AI Description -->
                                @if($snippet->ai_description)
                                    <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                                        <dt class="text-sm font-medium text-blue-700 dark:text-blue-300 transition-colors duration-200 mb-2">
                                            <i class="fas fa-brain mr-1.5"></i>
                                            AI Generated Description
                                        </dt>
                                        <dd class="text-sm text-blue-900 dark:text-blue-100 leading-relaxed">
                                            {{ $snippet->ai_description }}
                                        </dd>
                                    </div>
                                @endif

                                <!-- User Tags (display only) -->
                                @php
                                    $userTags = $snippet->user_tags;
                                    if (is_string($userTags)) {
                                        $decoded = json_decode($userTags, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $userTags = $decoded;
                                        } else {
                                            $userTags = [];
                                        }
                                    }
                                @endphp
                                <div class="mb-4">
                                    <dt class="text-sm font-semibold text-green-700 dark:text-green-300 transition-colors duration-200 mb-2">Your Tags</dt>
                                    <dd class="flex flex-wrap gap-2 mb-2">
                                        @if($userTags && count($userTags) > 0)
                                            @foreach($userTags as $tag)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-700 transition-colors duration-200">
                                                    <i class="fas fa-user-tag mr-1.5 text-xs"></i>
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-gray-400">No tags yet.</span>
                                        @endif
                                    </dd>
                                </div>

                                <!-- AI tags and quality score sections removed -->

                                <div class="text-xs text-gray-400 dark:text-gray-500 transition-colors duration-200">
                                    <i class="fas fa-clock mr-1"></i>
                                    Analyzed {{ $snippet->ai_processed_at->diffForHumans() }}
                                </div>
                            </div>

                        @elseif($snippet->isAIProcessing() && $aiAutoDescriptionEnabled)
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 dark:border-indigo-400 mr-2"></div>
                                AI analysis in progress...
                            </div>

                        @elseif($snippet->hasAIProcessingFailed())
                            <div class="flex items-center justify-between text-sm text-red-600 dark:text-red-400 transition-colors duration-200">
                                <span>
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    AI analysis failed
                                </span>
                                @can('update', $snippet)
                                    <button onclick="regenerateAI()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-200">
                                        Retry
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Share Modal (hidden by default) -->
    <div id="share-modal" class="hidden fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 transition-all duration-200">
        <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-600 w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 transition-colors duration-200">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200">Share Snippet</h3>
                <div class="space-y-4">
                    <div id="share-url-section" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">Public URL</label>
                        <div class="flex">
                            <input type="text" id="share-url" value=""
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-l-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200" readonly>
                            <button id="copy-share-btn" onclick="copyShareUrl()"
                                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded-r-lg text-sm font-medium transition-colors duration-200">
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">Anyone with this link can view the snippet without signing in.</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-200">Public sharing</span>
                        <button id="toggle-share-btn" onclick="togglePublicShare()"
                                class="px-3 py-1 text-sm rounded-lg transition-colors duration-200 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300">
                            <span id="share-status">Loading...</span>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="toggleShare()"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- History Sidebar -->
    <div id="history-sidebar" class="hidden fixed inset-y-0 right-0 w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-600 shadow-lg z-50 overflow-y-auto transition-colors duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">Version History</h3>
                <button onclick="toggleHistory()" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                @php
                    $allVersions = $snippet->versions->sortByDesc('version_number');
                    $latestVersionNumber = $snippet->versions->max('version_number');
                    // Get the version before the latest (skip the most recent version as it matches current content)
                    $previousVersion = $allVersions->where('version_number', '<', $latestVersionNumber)->first();
                @endphp

                <!-- Current Version -->
                @if($previousVersion)
                    <div class="border border-indigo-200 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg p-4 cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900/75 transition-colors duration-200"
                         onclick="selectVersion('current', {{ Js::from($previousVersion->content) }}, {{ Js::from($snippet->content) }}, 'Version {{ $previousVersion->version_number }}', 'Current Version', '{{ $previousVersion->created_at->format('M j, Y \a\t g:i A') }}', '{{ $snippet->updated_at->format('M j, Y \a\t g:i A') }}', '{{ $previousVersion->creator->name }}', '{{ $snippet->creator->name }}')">
                @else
                    <div class="border border-indigo-200 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg p-4 cursor-not-allowed opacity-60 transition-colors duration-200">
                @endif
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-indigo-800 dark:text-indigo-200 transition-colors duration-200">Current Version</span>
                        <span class="text-xs text-indigo-600 dark:text-indigo-300 bg-indigo-200 dark:bg-indigo-700 px-2 py-1 rounded transition-colors duration-200">Latest</span>
                    </div>
                    <div class="text-xs text-indigo-600 dark:text-indigo-300 transition-colors duration-200">
                        <div>Updated {{ $snippet->updated_at->diffForHumans() }}</div>
                        <div>by {{ $snippet->creator->name }}</div>
                    </div>
                </div>

                @php
                    // Exclude the latest version from historical versions since it's already shown as "Current Version"
                    $historicalVersions = $allVersions->where('version_number', '<', $latestVersionNumber);
                @endphp

                @if($historicalVersions->count() > 0)
                    @foreach($historicalVersions as $version)
                        @php
                            // Get the previous version (one version number lower)
                            $prevVersion = $allVersions->where('version_number', '<', $version->version_number)->sortByDesc('version_number')->first();
                        @endphp
                        @if($prevVersion)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                 onclick="selectVersion('{{ $version->id }}', {{ Js::from($prevVersion->content) }}, {{ Js::from($version->content) }}, 'Version {{ $prevVersion->version_number }}', 'Version {{ $version->version_number }}', '{{ $prevVersion->created_at->format('M j, Y \a\t g:i A') }}', '{{ $version->created_at->format('M j, Y \a\t g:i A') }}', '{{ $prevVersion->creator->name }}', '{{ $version->creator->name }}')">
                        @else
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                 onclick="selectVersion('{{ $version->id }}', '', {{ Js::from($version->content) }}, 'Initial', 'Version {{ $version->version_number }}', '', '{{ $version->created_at->format('M j, Y \a\t g:i A') }}', '', '{{ $version->creator->name }}')">
                        @endif
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">Version {{ $version->version_number }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $version->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                <div>Created {{ $version->created_at->format('M j, Y \a\t g:i A') }}</div>
                                <div>by {{ $version->creator->name }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No version history available</p>
                        <p class="text-xs mt-1">Edit this snippet to create versions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Comparison Modal -->
    <div id="comparison-modal" class="hidden fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 transition-all duration-200">
        <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-600 w-11/12 max-w-7xl shadow-lg rounded-md bg-white dark:bg-gray-800 transition-colors duration-200">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">Version Comparison</h3>
                    <button onclick="closeComparison()" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-600 rounded-lg p-3 transition-colors duration-200">
                        <h4 class="font-medium text-red-800 dark:text-red-200 mb-2 transition-colors duration-200">Previous Version</h4>
                        <select id="comparison-old-select" onchange="updateComparison()"
                                class="w-full px-3 py-2 text-sm border border-red-300 dark:border-red-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors duration-200">
                        </select>
                        <p class="text-xs text-red-600 dark:text-red-300 mt-2 transition-colors duration-200" id="comparison-old-meta">Metadata</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-600 rounded-lg p-3 transition-colors duration-200">
                        <h4 class="font-medium text-green-800 dark:text-green-200 mb-2 transition-colors duration-200">Compare With</h4>
                        <select id="comparison-new-select" onchange="updateComparison()"
                                class="w-full px-3 py-2 text-sm border border-green-300 dark:border-green-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200">
                        </select>
                        <p class="text-xs text-green-600 dark:text-green-300 mt-2 transition-colors duration-200" id="comparison-new-meta">Metadata</p>
                    </div>
                </div>

                <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden transition-colors duration-200">
                    <div id="diff-editor" style="height: 600px;"></div>
                </div>

                <div class="flex justify-end mt-4">
                    <button onclick="closeComparison()"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clone Modal -->
    <div id="clone-modal" class="hidden fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 transition-all duration-200">
        <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-600 w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800 transition-colors duration-200">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">Clone Snippet</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    Create a copy of this snippet in your personal collection or a team.
                </p>
            </div>

            <form id="clone-form" action="{{ route('snippets.clone', $snippet) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Title
                    </label>
                    <input type="text" name="title"
                           value="{{ $snippet->title }} (Copy)"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Destination
                    </label>
                    <select name="owner_type" id="clone-owner-type" onchange="updateCloneFolders()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200">
                        <option value="personal">My Personal Snippets</option>
                        @foreach($availableTeams ?? [] as $team)
                            <option value="team" data-team-id="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="team_id" id="clone-team-id">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Folder <span class="text-gray-400">(Optional)</span>
                    </label>
                    <select name="folder_id" id="clone-folder-id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200">
                        <option value="">No Folder</option>
                        <!-- Folders will be populated dynamically -->
                    </select>
                </div>

                <div class="flex justify-end space-x-3 mt-5">
                    <button type="button" onclick="closeCloneModal()"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded font-medium transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded font-medium transition-colors duration-200">
                        <i class="fas fa-clone mr-2"></i>
                        Clone Snippet
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Delete Form (hidden) -->
    @can('delete', $snippet)
        <form id="delete-form" action="{{ route('snippets.destroy', $snippet) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan
</div>

<!-- Monaco Editor -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs/loader.js"></script>
@include('partials.monaco-theme-loader')

<script>
function copyToClipboard() {
    let code;

    // Get code from Monaco Editor if available, otherwise use the snippet content
    if (window.monacoEditor) {
        code = window.monacoEditor.getValue();
    } else {
        code = {{ Js::from($snippet->content) }};
    }

    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Clipboard API failed: ', err);
            fallbackCopy(code);
        });
    } else {
        // Use fallback method
        fallbackCopy(code);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        console.error('Fallback copy failed: ', err);
        alert('Copy failed. Please select the text manually and copy.');
    }

    document.body.removeChild(textArea);
}

function showCopySuccess() {
    // Show success message for all copy buttons
    const buttons = document.querySelectorAll('button[onclick="copyToClipboard()"]');
    buttons.forEach(button => {
        const originalText = button.textContent.trim();
        const originalClasses = button.className;

        button.textContent = 'Copied!';
        button.classList.add('bg-green-100', 'text-green-700');
        button.classList.remove('hover:bg-gray-200', 'hover:bg-gray-100');

        setTimeout(() => {
            button.textContent = originalText;
            button.className = originalClasses;
        }, 2000);
    });
}

function regenerateAI() {
    const regenerateBtn = document.getElementById('regenerate-btn');
    if (!regenerateBtn) return;

    // Update button state
    const originalText = regenerateBtn.innerHTML;
    regenerateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
    regenerateBtn.disabled = true;

    // Make API request
    fetch(`{{ route('snippets.processAI', $snippet) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            regenerateBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Started';
            regenerateBtn.classList.add('text-green-600', 'dark:text-green-400');

            // Show a toast or alert
            if (data.message) {
                // Create a simple toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded z-50 transition-colors duration-200';
                toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${data.message}`;
                document.body.appendChild(toast);

                // Auto remove toast after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);

                // Reload page after a brief delay to show updated AI analysis
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        } else {
            throw new Error(data.message || 'Failed to start AI processing');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        regenerateBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Failed';
        regenerateBtn.classList.add('text-red-600', 'dark:text-red-400');

        // Show error toast
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 px-4 py-3 rounded z-50 transition-colors duration-200';
        toast.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>Failed to start AI processing`;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    })
    .finally(() => {
        // Reset button after delay
        setTimeout(() => {
            regenerateBtn.innerHTML = originalText;
            regenerateBtn.disabled = false;
            regenerateBtn.classList.remove('text-green-600', 'dark:text-green-400', 'text-red-600', 'dark:text-red-400');
        }, 2000);
    });
}

function toggleShare() {
    const modal = document.getElementById('share-modal');
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        loadShareStatus();
    } else {
        modal.classList.add('hidden');
    }
}

function loadShareStatus() {
    // Add a small delay to ensure modal elements are rendered
    setTimeout(() => {
        loadShareStatusOnly();
    }, 100);
}

function loadShareStatusOnly() {
    // Load share status dynamically from server
    fetch(`/snippets/{{ $snippet->id }}/share-status`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Share status loaded:', data);
        if (data.success) {
            updateShareUI(data.shared, data.uuid);
        } else {
            updateShareUI(false, null);
        }
    })
    .catch(error => {
        console.error('Error loading share status:', error);
        updateShareUI(false, null);
    });
}

function updateShareUI(isShared, uuid = null) {
    console.log('updateShareUI called:', { isShared, uuid });
    const shareUrl = document.getElementById('share-url');
    const shareStatus = document.getElementById('share-status');
    const toggleBtn = document.getElementById('toggle-share-btn');
    const shareUrlSection = document.getElementById('share-url-section');

    // Add null checks to prevent errors
    if (!shareUrl || !shareStatus || !toggleBtn || !shareUrlSection) {
        console.warn('Share UI elements not found:', { shareUrl: !!shareUrl, shareStatus: !!shareStatus, toggleBtn: !!toggleBtn, shareUrlSection: !!shareUrlSection });
        return;
    }

    if (isShared && uuid) {
        console.log('Setting UI to shared state');
        shareUrl.value = `${window.location.origin}/s/${uuid}`;
        shareStatus.textContent = 'Enabled';
        toggleBtn.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
        toggleBtn.classList.add('bg-green-100', 'hover:bg-green-200', 'text-green-700');
        shareUrlSection.classList.remove('hidden');
    } else {
        console.log('Setting UI to disabled state');
        shareUrl.value = '';
        shareStatus.textContent = 'Disabled';
        toggleBtn.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-700');
        toggleBtn.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
        shareUrlSection.classList.add('hidden');
    }
}

function togglePublicShare() {
    const toggleBtn = document.getElementById('toggle-share-btn');

    if (!toggleBtn) {
        console.error('Toggle share button not found');
        return;
    }

    const originalText = toggleBtn.innerHTML;
    toggleBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>';

    fetch(`/snippets/{{ $snippet->id }}/toggle-share`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            // Update UI immediately with server response
            updateShareUI(data.shared, data.uuid);

            // Also reload the share status to ensure UI is in sync
            setTimeout(() => {
                loadShareStatusOnly();
            }, 500);

            if (data.shared) {
                showToast('Public sharing enabled!', 'success');
            } else {
                showToast('Public sharing disabled!', 'info');
            }
        } else {
            showToast('Failed to update sharing settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        if (toggleBtn) {
            toggleBtn.innerHTML = originalText;
        }
    });
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm font-medium z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function toggleHistory() {
    const sidebar = document.getElementById('history-sidebar');
    const body = document.body;

    if (sidebar.classList.contains('hidden')) {
        sidebar.classList.remove('hidden');
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'history-backdrop';
        backdrop.onclick = toggleHistory;
        backdrop.id = 'history-backdrop';
        body.appendChild(backdrop);
    } else {
        sidebar.classList.add('hidden');
        // Remove backdrop
        const backdrop = document.getElementById('history-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

let diffEditor;
let selectedVersion = null;
let allVersionsData = [];

// Initialize versions data
document.addEventListener('DOMContentLoaded', function() {
    @php
        $latestVersionNumber = $snippet->versions->max('version_number');
        $historicalVersionsForDropdown = $snippet->versions->where('version_number', '<', $latestVersionNumber)->sortByDesc('version_number');
    @endphp

    // Build versions array from PHP data (exclude latest version as it matches current)
    allVersionsData = [
        {
            id: 'current',
            number: 'Current',
            content: {{ Js::from($snippet->content) }},
            date: '{{ $snippet->updated_at->format('M j, Y \a\t g:i A') }}',
            author: '{{ $snippet->creator->name }}',
            title: 'Current Version'
        },
        @foreach($historicalVersionsForDropdown as $version)
        {
            id: '{{ $version->id }}',
            number: {{ $version->version_number }},
            content: {{ Js::from($version->content) }},
            date: '{{ $version->created_at->format('M j, Y \a\t g:i A') }}',
            author: '{{ $version->creator->name }}',
            title: 'Version {{ $version->version_number }}'
        },
        @endforeach
    ];
});

function selectVersion(versionId, oldContent, newContent, oldTitle, newTitle, oldDate, newDate, oldAuthor, newAuthor) {
    selectedVersion = {
        id: versionId,
        oldContent: oldContent,
        newContent: newContent,
        oldTitle: oldTitle,
        newTitle: newTitle,
        oldDate: oldDate,
        newDate: newDate,
        oldAuthor: oldAuthor,
        newAuthor: newAuthor
    };

    showComparison();
}

function showComparison() {
    if (!selectedVersion) return;

    const modal = document.getElementById('comparison-modal');

    // Populate dropdowns
    populateVersionDropdowns();

    // Set initial selections based on selectedVersion
    setInitialDropdownSelections();

    modal.classList.remove('hidden');

    // Initialize diff editor with a check for Monaco availability
    setTimeout(() => {
        if (typeof monaco !== 'undefined') {
            updateComparison();
        } else {
            console.error('Monaco editor is not available for diff view');
        }
    }, 100);
}

function populateVersionDropdowns() {
    const oldSelect = document.getElementById('comparison-old-select');
    const newSelect = document.getElementById('comparison-new-select');

    // Clear existing options
    oldSelect.innerHTML = '';
    newSelect.innerHTML = '';

    // Populate both dropdowns with all versions
    allVersionsData.forEach(version => {
        const option1 = new Option(version.title, version.id);
        const option2 = new Option(version.title, version.id);
        oldSelect.add(option1);
        newSelect.add(option2);
    });
}

function setInitialDropdownSelections() {
    // Find which versions match the selected comparison
    const oldSelect = document.getElementById('comparison-old-select');
    const newSelect = document.getElementById('comparison-new-select');

    // Try to match by content or title
    for (let i = 0; i < allVersionsData.length; i++) {
        if (allVersionsData[i].content === selectedVersion.oldContent) {
            oldSelect.selectedIndex = i;
        }
        if (allVersionsData[i].content === selectedVersion.newContent) {
            newSelect.selectedIndex = i;
        }
    }
}

function updateComparison() {
    const oldSelect = document.getElementById('comparison-old-select');
    const newSelect = document.getElementById('comparison-new-select');

    const oldVersionId = oldSelect.value;
    const newVersionId = newSelect.value;

    const oldVersion = allVersionsData.find(v => v.id == oldVersionId);
    const newVersion = allVersionsData.find(v => v.id == newVersionId);

    if (!oldVersion || !newVersion) return;

    // Update metadata displays
    document.getElementById('comparison-old-meta').textContent = oldVersion.date ? `${oldVersion.date} by ${oldVersion.author}` : 'Initial version';
    document.getElementById('comparison-new-meta').textContent = `${newVersion.date} by ${newVersion.author}`;

    // Update diff editor
    if (typeof monaco !== 'undefined') {
        initializeDiffEditor(oldVersion.content, newVersion.content);
    }
}

function closeComparison() {
    const modal = document.getElementById('comparison-modal');
    modal.classList.add('hidden');

    if (diffEditor) {
        diffEditor.dispose();
        diffEditor = null;
    }
}

async function initializeDiffEditor(originalContent, modifiedContent) {
    // Check if Monaco is available
    if (typeof monaco === 'undefined') {
        console.error('Monaco editor is not loaded yet');
        return;
    }

    if (diffEditor) {
        diffEditor.dispose();
    }

    const currentLanguage = '{{ $snippet->language }}';

    // Map our language values to Monaco language IDs
    const languageMap = {
        'javascript': 'javascript',
        'typescript': 'typescript',
        'python': 'python',
        'php': 'php',
        'java': 'java',
        'csharp': 'csharp',
        'cpp': 'cpp',
        'c': 'c',
        'go': 'go',
        'rust': 'rust',
        'ruby': 'ruby',
        'swift': 'swift',
        'kotlin': 'kotlin',
        'html': 'html',
        'css': 'css',
        'sql': 'sql',
        'bash': 'shell',
        'powershell': 'powershell',
        'json': 'json',
        'yaml': 'yaml',
        'xml': 'xml',
        'markdown': 'markdown'
    };

    const monacoLanguage = languageMap[currentLanguage] || 'plaintext';

    // Function to load user's preferred theme
    async function getUserTheme() {
        const themeName = await loadMonacoTheme(userMonacoTheme);
        return themeName;
    }

    const diffTheme = await getUserTheme();

    diffEditor = monaco.editor.createDiffEditor(document.getElementById('diff-editor'), {
        theme: diffTheme,
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: false },
        scrollBeyondLastLine: false,
        wordWrap: 'on',
        readOnly: true,
        renderSideBySide: true,
        ignoreTrimWhitespace: false,
        renderIndicators: true,
        originalEditable: false,
        modifiedEditable: false,
        scrollbar: {
            alwaysConsumeMouseWheel: false,
            handleMouseWheel: true
        },
        mouseWheelZoom: false,
        fastScrollSensitivity: 5
    });

    // Explicitly set theme for diff editor
    monaco.editor.setTheme(diffTheme);

    const originalModel = monaco.editor.createModel(originalContent, monacoLanguage);
    const modifiedModel = monaco.editor.createModel(modifiedContent, monacoLanguage);

    diffEditor.setModel({
        original: originalModel,
        modified: modifiedModel
    });

    // Listen for theme changes and update diff editor theme
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const newTheme = getCurrentTheme();
                if (diffEditor && typeof monaco !== 'undefined') {
                    monaco.editor.setTheme(newTheme);
                }
            }
        });
    });

    // Start observing changes to the html element's class list
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

function copyShareUrl() {
    const urlInput = document.getElementById('share-url');
    urlInput.select();
    document.execCommand('copy');

    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}

function downloadSnippet() {
    const content = {{ Js::from($snippet->content) }};
    const language = {{ Js::from($snippet->language) }};
    const title = {{ Js::from($snippet->title) }};

    // Get file extension based on language
    const extensions = {
        'javascript': 'js',
        'typescript': 'ts',
        'python': 'py',
        'php': 'php',
        'java': 'java',
        'csharp': 'cs',
        'cpp': 'cpp',
        'c': 'c',
        'go': 'go',
        'rust': 'rs',
        'ruby': 'rb',
        'swift': 'swift',
        'kotlin': 'kt',
        'html': 'html',
        'css': 'css',
        'sql': 'sql',
        'bash': 'sh',
        'powershell': 'ps1',
        'json': 'json',
        'yaml': 'yml',
        'xml': 'xml',
        'markdown': 'md'
    };

    const extension = extensions[language] || 'txt';
    const filename = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.' + extension;

    const element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this snippet? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}

// Initialize Monaco Editor
function initializeMonaco() {
    if (typeof require !== 'undefined') {
        require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs' }});

        require(['vs/editor/editor.main'], async function() {
            const snippetContent = {{ Js::from($snippet->content) }};
            const currentLanguage = '{{ $snippet->language }}';

            // Map our language values to Monaco language IDs
            const languageMap = {
                'javascript': 'javascript',
                'typescript': 'typescript',
                'python': 'python',
                'php': 'php',
                'java': 'java',
                'csharp': 'csharp',
                'cpp': 'cpp',
                'c': 'c',
                'go': 'go',
                'rust': 'rust',
                'ruby': 'ruby',
                'swift': 'swift',
                'kotlin': 'kotlin',
                'html': 'html',
                'css': 'css',
                'sql': 'sql',
                'bash': 'shell',
                'powershell': 'powershell',
                'json': 'json',
                'yaml': 'yaml',
                'xml': 'xml',
                'markdown': 'markdown'
            };

            const monacoLanguage = languageMap[currentLanguage] || 'plaintext';

            // Load user's preferred theme
            const themeName = await loadMonacoTheme(userMonacoTheme);

            window.monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: snippetContent,
            language: monacoLanguage,
            theme: themeName,
            readOnly: true,
            automaticLayout: true,
            fontSize: 14,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            lineNumbers: 'on',
            folding: true,
            bracketMatching: 'always',
            contextmenu: false,
            selectOnLineNumbers: true,
            scrollbar: {
                alwaysConsumeMouseWheel: false,
                handleMouseWheel: true
            },
            mouseWheelZoom: false,
            fastScrollSensitivity: 5
        });

        // Explicitly set theme after editor creation
        monaco.editor.setTheme(themeName);

        // Listen for theme changes and update Monaco editor
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const newTheme = getCurrentTheme();
                    monaco.editor.setTheme(newTheme);
                }
            });
        });

            // Start observing changes to the html element's class list
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    } else {
        console.log('Monaco loader not ready, retrying in 100ms...');
        setTimeout(initializeMonaco, 100);
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeMonaco();
});

// Clone modal functions
const cloneFoldersData = @json($cloneFoldersData ?? ['personal' => [], 'teams' => []]);

function openCloneModal() {
    document.getElementById('clone-modal').classList.remove('hidden');
    updateCloneFolders();
}

function closeCloneModal() {
    document.getElementById('clone-modal').classList.add('hidden');
}

function updateCloneFolders() {
    const ownerTypeSelect = document.getElementById('clone-owner-type');
    const selectedOption = ownerTypeSelect.options[ownerTypeSelect.selectedIndex];
    const teamIdInput = document.getElementById('clone-team-id');
    const folderSelect = document.getElementById('clone-folder-id');

    // Clear folder options
    folderSelect.innerHTML = '<option value="">No Folder</option>';

    if (selectedOption.value === 'personal') {
        teamIdInput.value = '';
        // Add personal folders
        if (cloneFoldersData.personal && cloneFoldersData.personal.length > 0) {
            cloneFoldersData.personal.forEach(folder => {
                const option = document.createElement('option');
                option.value = folder.id;
                option.textContent = folder.name;
                folderSelect.appendChild(option);
            });
        }
    } else {
        const teamId = selectedOption.dataset.teamId;
        teamIdInput.value = teamId;
        // Add team folders
        if (cloneFoldersData.teams && cloneFoldersData.teams[teamId]) {
            cloneFoldersData.teams[teamId].forEach(folder => {
                const option = document.createElement('option');
                option.value = folder.id;
                option.textContent = folder.name;
                folderSelect.appendChild(option);
            });
        }
    }
}
</script>

@endsection
