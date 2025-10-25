@extends('layouts.snippets')

@section('title', 'Create Snippet')

@push('styles')
    <style>
        .monaco-container {
            height: 400px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Create New Snippet</h1>
        <p class="text-gray-600 mt-1">Save and organize your code snippets</p>
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

        <form action="{{ route('snippets.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Snippet Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
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
                        <option value="javascript" {{ old('language') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                        <option value="typescript" {{ old('language') == 'typescript' ? 'selected' : '' }}>TypeScript</option>
                        <option value="python" {{ old('language') == 'python' ? 'selected' : '' }}>Python</option>
                        <option value="php" {{ old('language') == 'php' ? 'selected' : '' }}>PHP</option>
                        <option value="java" {{ old('language') == 'java' ? 'selected' : '' }}>Java</option>
                        <option value="csharp" {{ old('language') == 'csharp' ? 'selected' : '' }}>C#</option>
                        <option value="cpp" {{ old('language') == 'cpp' ? 'selected' : '' }}>C++</option>
                        <option value="c" {{ old('language') == 'c' ? 'selected' : '' }}>C</option>
                        <option value="go" {{ old('language') == 'go' ? 'selected' : '' }}>Go</option>
                        <option value="rust" {{ old('language') == 'rust' ? 'selected' : '' }}>Rust</option>
                        <option value="ruby" {{ old('language') == 'ruby' ? 'selected' : '' }}>Ruby</option>
                        <option value="swift" {{ old('language') == 'swift' ? 'selected' : '' }}>Swift</option>
                        <option value="kotlin" {{ old('language') == 'kotlin' ? 'selected' : '' }}>Kotlin</option>
                        <option value="html" {{ old('language') == 'html' ? 'selected' : '' }}>HTML</option>
                        <option value="css" {{ old('language') == 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="sql" {{ old('language') == 'sql' ? 'selected' : '' }}>SQL</option>
                        <option value="bash" {{ old('language') == 'bash' ? 'selected' : '' }}>Bash</option>
                        <option value="powershell" {{ old('language') == 'powershell' ? 'selected' : '' }}>PowerShell</option>
                        <option value="json" {{ old('language') == 'json' ? 'selected' : '' }}>JSON</option>
                        <option value="yaml" {{ old('language') == 'yaml' ? 'selected' : '' }}>YAML</option>
                        <option value="xml" {{ old('language') == 'xml' ? 'selected' : '' }}>XML</option>
                        <option value="markdown" {{ old('language') == 'markdown' ? 'selected' : '' }}>Markdown</option>
                        <option value="other" {{ old('language') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('language')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Owner Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Owner <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <!-- Personal Snippet -->
                        <label class="flex items-center">
                            <input type="radio"
                                   name="owner_type"
                                   value="personal"
                                   class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('owner_type', 'personal') == 'personal' ? 'checked' : '' }}
                                   onchange="updateOwnerOptions()">
                            <span class="ml-2 text-sm text-gray-700">Personal Snippet</span>
                        </label>

                        <!-- Team Snippet -->
                        @if($teams->count() > 0)
                            <label class="flex items-center">
                                <input type="radio"
                                       name="owner_type"
                                       value="team"
                                       class="text-indigo-600 focus:ring-indigo-500"
                                       {{ old('owner_type') == 'team' ? 'checked' : '' }}
                                       onchange="updateOwnerOptions()">
                                <span class="ml-2 text-sm text-gray-700">Team Snippet</span>
                            </label>
                        @endif
                    </div>
                    @error('owner_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Team Selection -->
                @if($teams->count() > 0)
                    <div id="team-selection" style="{{ old('owner_type') == 'team' ? '' : 'display: none;' }}">
                        <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Team <span class="text-red-500">*</span>
                        </label>
                        <select id="team_id"
                                name="team_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('team_id') border-red-500 @enderror">
                            <option value="">Choose a team...</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
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
                            <option value="{{ $folder->id }}" {{ old('folder_id', request('folder_id')) == $folder->id ? 'selected' : '' }}>
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
            @else
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>No folders available.</strong>
                        <a href="{{ route('folders.create') }}" class="text-yellow-900 underline hover:no-underline">Create a folder</a>
                        first to organize your snippets.
                    </p>
                </div>
            @endif

            <!-- Code Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Code <span class="text-red-500">*</span>
                </label>

                <!-- Monaco Editor Container -->
                <div id="monaco-editor" class="monaco-container"></div>

                <!-- Hidden textarea for form submission -->
                <textarea id="content"
                          name="content"
                          class="hidden @error('content') border-red-500 @enderror"
                          required>{{ old('content') }}</textarea>

                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('snippets.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Create Snippet
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <!-- Monaco Editor -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
@endpush

<script>
let monacoEditor;

function updateOwnerOptions() {
    const ownerTypeRadios = document.getElementsByName('owner_type');
    const teamSelection = document.getElementById('team-selection');

    for (let radio of ownerTypeRadios) {
        if (radio.checked) {
            if (radio.value === 'team') {
                teamSelection.style.display = 'block';
            } else {
                teamSelection.style.display = 'none';
            }
            break;
        }
    }
}

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
        monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: '// Start typing your code here...',
            language: 'javascript',
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

        // Set initial content if there's old input
        const oldContent = {{ Js::from(old('content', '')) }};
        if (oldContent) {
            monacoEditor.setValue(oldContent);
        }

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
</script>
@endsection
