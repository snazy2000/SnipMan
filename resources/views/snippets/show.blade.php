@extends('layouts.snippets')

@section('title', $snippet->title)

@push('styles')
    <style>
        .code-container {
            position: relative;
        }
    </style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <nav class="text-sm breadcrumbs mb-4">
            <a href="{{ route('snippets.index') }}" class="text-indigo-600 hover:text-indigo-800">Snippets</a>
            @if($snippet->folder)
                <span class="text-gray-400 mx-2">/</span>
                <a href="{{ route('folders.show', $snippet->folder) }}" class="text-indigo-600 hover:text-indigo-800">{{ $snippet->folder->name }}</a>
            @endif
            <span class="text-gray-400 mx-2">/</span>
            <span class="text-gray-900">{{ $snippet->title }}</span>
        </nav>

        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $snippet->title }}</h1>
                <div class="flex items-center mt-2 space-x-4 text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($snippet->language) }}
                    </span>
                    <span>
                        @if($snippet->owner_type === 'App\Models\Team')
                            Team: {{ $snippet->owner->name }}
                        @else
                            Personal Snippet
                        @endif
                    </span>
                    @if($snippet->folder)
                        <span>Folder: {{ $snippet->folder->name }}</span>
                    @endif
                    <span>Created by {{ $snippet->creator->name }}</span>
                    <span>{{ $snippet->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex space-x-3">
                <button onclick="copyToClipboard()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                    Copy Code
                </button>
                @can('update', $snippet)
                    <a href="{{ route('snippets.edit', $snippet) }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Edit
                    </a>
                @endcan
                <button onclick="toggleShare()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Share
                </button>
            </div>
        </div>
    </div>

    <!-- Code Display -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-8">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-gray-900">{{ $snippet->title }}</span>
                <span class="text-xs text-gray-500">{{ ucfirst($snippet->language) }}</span>
                <span class="text-xs text-gray-500">{{ strlen($snippet->content) }} characters</span>
                <span class="text-xs text-gray-500">{{ substr_count($snippet->content, "\n") + 1 }} lines</span>
            </div>
        </div>
        <div class="code-container">
            <!-- Monaco Editor Container (Read-only) -->
            <div id="monaco-editor" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Snippet Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Snippet Details</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Language</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($snippet->language) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Owner</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($snippet->owner_type === 'App\Models\Team')
                                {{ $snippet->owner->name }} (Team)
                            @else
                                Personal
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Folder</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($snippet->folder)
                                <a href="{{ route('folders.show', $snippet->folder) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $snippet->folder->name }}
                                </a>
                            @else
                                No folder
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created by</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $snippet->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $snippet->created_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $snippet->updated_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Actions -->
        <div>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    @can('update', $snippet)
                        <a href="{{ route('snippets.edit', $snippet) }}"
                           class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Snippet
                        </a>
                    @endcan

                    <button onclick="copyToClipboard()"
                            class="w-full flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Code
                    </button>

                    <button onclick="downloadSnippet()"
                            class="w-full flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </button>

                    @can('delete', $snippet)
                        <button onclick="confirmDelete()"
                                class="w-full flex justify-center items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal (hidden by default) -->
    <div id="share-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Share Snippet</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Public URL</label>
                        <div class="flex">
                            <input type="text" id="share-url" value="{{ route('snippets.show', $snippet) }}"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" readonly>
                            <button onclick="copyShareUrl()"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-r-lg text-sm font-medium transition-colors">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="toggleShare()"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Close
                    </button>
                </div>
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

@push('scripts')
    <!-- Monaco Editor -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
@endpush

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

function toggleShare() {
    const modal = document.getElementById('share-modal');
    modal.classList.toggle('hidden');
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

// Initialize Monaco Editor when page loads
document.addEventListener('DOMContentLoaded', function() {
    require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' }});

    require(['vs/editor/editor.main'], function() {
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

        window.monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: snippetContent,
            language: monacoLanguage,
            theme: 'vs',
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
            selectOnLineNumbers: true
        });
    });
});
</script>

@push('scripts')
    <!-- Monaco Editor -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
@endpush
@endsection
