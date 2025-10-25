@extends('layouts.snippets')

@section('title', 'Edit Snippet')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Snippet</h1>
        <p class="text-gray-600 mt-1">Update your code snippet</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-red-800 mb-2">There were some errors with your submission:</h3>
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('snippets.update', $snippet) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Snippet Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $snippet->title) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="Enter snippet title"
                           required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Language -->
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                        Language <span class="text-red-500">*</span>
                    </label>
                    <select id="language"
                            name="language"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('language') border-red-500 @enderror"
                            required>
                        <option value="">Select a language...</option>
                        <option value="javascript" {{ old('language', $snippet->language) == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                        <option value="typescript" {{ old('language', $snippet->language) == 'typescript' ? 'selected' : '' }}>TypeScript</option>
                        <option value="python" {{ old('language', $snippet->language) == 'python' ? 'selected' : '' }}>Python</option>
                        <option value="php" {{ old('language', $snippet->language) == 'php' ? 'selected' : '' }}>PHP</option>
                        <option value="java" {{ old('language', $snippet->language) == 'java' ? 'selected' : '' }}>Java</option>
                        <option value="csharp" {{ old('language', $snippet->language) == 'csharp' ? 'selected' : '' }}>C#</option>
                        <option value="cpp" {{ old('language', $snippet->language) == 'cpp' ? 'selected' : '' }}>C++</option>
                        <option value="c" {{ old('language', $snippet->language) == 'c' ? 'selected' : '' }}>C</option>
                        <option value="go" {{ old('language', $snippet->language) == 'go' ? 'selected' : '' }}>Go</option>
                        <option value="rust" {{ old('language', $snippet->language) == 'rust' ? 'selected' : '' }}>Rust</option>
                        <option value="ruby" {{ old('language', $snippet->language) == 'ruby' ? 'selected' : '' }}>Ruby</option>
                        <option value="swift" {{ old('language', $snippet->language) == 'swift' ? 'selected' : '' }}>Swift</option>
                        <option value="kotlin" {{ old('language', $snippet->language) == 'kotlin' ? 'selected' : '' }}>Kotlin</option>
                        <option value="html" {{ old('language', $snippet->language) == 'html' ? 'selected' : '' }}>HTML</option>
                        <option value="css" {{ old('language', $snippet->language) == 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="sql" {{ old('language', $snippet->language) == 'sql' ? 'selected' : '' }}>SQL</option>
                        <option value="bash" {{ old('language', $snippet->language) == 'bash' ? 'selected' : '' }}>Bash</option>
                        <option value="powershell" {{ old('language', $snippet->language) == 'powershell' ? 'selected' : '' }}>PowerShell</option>
                        <option value="json" {{ old('language', $snippet->language) == 'json' ? 'selected' : '' }}>JSON</option>
                        <option value="yaml" {{ old('language', $snippet->language) == 'yaml' ? 'selected' : '' }}>YAML</option>
                        <option value="xml" {{ old('language', $snippet->language) == 'xml' ? 'selected' : '' }}>XML</option>
                        <option value="markdown" {{ old('language', $snippet->language) == 'markdown' ? 'selected' : '' }}>Markdown</option>
                        <option value="other" {{ old('language', $snippet->language) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('language')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Owner Information (read-only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Owner</label>
                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                    @if($snippet->owner_type === 'App\Models\Team')
                        Team: {{ $snippet->owner->name }}
                    @else
                        Personal Snippet
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">Owner cannot be changed after creation</p>
            </div>

            <!-- Folder -->
            @if($folders->count() > 0)
                <div class="mb-6">
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Folder <span class="text-red-500">*</span>
                    </label>
                    <select id="folder_id"
                            name="folder_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('folder_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a folder...</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ old('folder_id', $snippet->folder_id) == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                                @if($folder->owner_type === 'App\Models\Team')
                                    (Team: {{ $folder->owner->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('folder_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Code Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Code <span class="text-red-500">*</span>
                </label>
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                    <!-- Monaco Editor Container -->
                    <div id="monaco-editor" style="height: 500px;"></div>

                    <!-- Hidden textarea for form submission -->
                    <textarea id="content"
                              name="content"
                              class="hidden"
                              required>{{ old('content', $snippet->content) }}</textarea>
                </div>
                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Snippet Stats -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Snippet Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Created:</span>
                        <span class="font-medium text-gray-900">{{ $snippet->created_at->format('M j, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Last updated:</span>
                        <span class="font-medium text-gray-900">{{ $snippet->updated_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex space-x-3">
                    <a href="{{ route('snippets.show', $snippet) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </a>
                    @can('delete', $snippet)
                        <button type="button"
                                onclick="confirmDelete()"
                                class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            Delete Snippet
                        </button>
                    @endcan
                </div>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Update Snippet
                </button>
            </div>
        </form>

        <!-- Delete Form (hidden) -->
        @can('delete', $snippet)
            <form id="delete-form" action="{{ route('snippets.destroy', $snippet) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this snippet? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>

@push('scripts')
    <!-- Monaco Editor -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
@endpush

<script>
let monacoEditor;

function updateEditorLanguage() {
    const languageSelect = document.getElementById('language');
    const selectedLanguage = languageSelect.value;

    if (monacoEditor && selectedLanguage) {
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

        const monacoLanguage = languageMap[selectedLanguage] || 'plaintext';
        monaco.editor.setModelLanguage(monacoEditor.getModel(), monacoLanguage);
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

        monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: snippetContent,
            language: monacoLanguage,
            theme: 'vs',
            automaticLayout: true,
            fontSize: 14,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            lineNumbers: 'on',
            folding: true,
            bracketMatching: 'always',
            autoIndent: 'full',
            formatOnPaste: true,
            formatOnType: true
        });

        // Update hidden textarea when editor content changes
        monacoEditor.onDidChangeModelContent(function() {
            document.getElementById('content').value = monacoEditor.getValue();
        });

        // Update language when dropdown changes
        document.getElementById('language').addEventListener('change', updateEditorLanguage);
    });
});

// Update hidden textarea before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (monacoEditor) {
        document.getElementById('content').value = monacoEditor.getValue();
    }
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this snippet? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
