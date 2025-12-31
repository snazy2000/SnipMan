@extends('layouts.snippets')

@section('title', 'Edit Snippet')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">Edit Snippet</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors duration-200">Update your code snippet</p>
</div>

    <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-6 transition-colors duration-200">
        @if($errors->any())
            <div class="mb-6 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-600 rounded-lg p-4 transition-colors duration-200">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2 transition-colors duration-200">There were some errors with your submission:</h3>
                <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside transition-colors duration-200">
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
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $snippet->title) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('title') border-red-500 @enderror"
                           placeholder="Enter snippet title"
                           required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Language -->
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Language <span class="text-red-500">*</span>
                    </label>
                    <select id="language"
                            name="language"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('language') border-red-500 @enderror"
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">Owner</label>
                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    @if($snippet->owner_type === 'App\Models\Team')
                        Team: {{ $snippet->owner->name }}
                    @else
                        Personal Snippet
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">Owner cannot be changed after creation</p>
            </div>

            <!-- Folder -->
            @if($personalFolders->count() > 0 || $teamFolders->count() > 0)
                <div class="mb-6">
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Folder <span class="text-red-500">*</span>
                    </label>
                    <select id="folder_id"
                            name="folder_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('folder_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a folder...</option>

                        @if($snippet->owner_type === 'App\Models\User')
                            {{-- Personal Folders --}}
                            @foreach($personalFolders as $folder)
                                <option value="{{ $folder->id }}" {{ old('folder_id', $snippet->folder_id) == $folder->id ? 'selected' : '' }}>
                                    {{ $folder->name }}
                                </option>
                                @foreach($folder->children as $child)
                                    <option value="{{ $child->id }}" {{ old('folder_id', $snippet->folder_id) == $child->id ? 'selected' : '' }}>
                                        └─ {{ $child->name }}
                                    </option>
                                    @foreach($child->children as $grandchild)
                                        <option value="{{ $grandchild->id }}" {{ old('folder_id', $snippet->folder_id) == $grandchild->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;└─ {{ $grandchild->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            @endforeach
                        @else
                            {{-- Team Folders --}}
                            @foreach($teamFolders as $folder)
                                @if($folder->owner_id === $snippet->owner_id)
                                    <option value="{{ $folder->id }}" {{ old('folder_id', $snippet->folder_id) == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->name }}
                                    </option>
                                    @foreach($folder->children as $child)
                                        <option value="{{ $child->id }}" {{ old('folder_id', $snippet->folder_id) == $child->id ? 'selected' : '' }}>
                                            └─ {{ $child->name }}
                                        </option>
                                        @foreach($child->children as $grandchild)
                                            <option value="{{ $grandchild->id }}" {{ old('folder_id', $snippet->folder_id) == $grandchild->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;└─ {{ $grandchild->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </select>
                    @error('folder_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Code Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                    Code <span class="text-red-500">*</span>
                </label>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden transition-colors duration-200">
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

            <!-- User Tags (Alpine.js) -->
            <script>
                window._snippetUserTags = (() => {
                    let t = @json(old('user_tags', $snippet->user_tags ?? []));
                    if (typeof t === 'string') {
                        try { t = JSON.parse(t); } catch (e) { t = t ? t.split(',').map(x => x.trim()).filter(Boolean) : []; }
                    }
                    if (!Array.isArray(t)) t = [];
                    return t;
                })();
            </script>
            <div class="mb-6"
                x-data="tagInput({ tags: window._snippetUserTags, input: '' })"
            >
                <label class="block text-sm font-medium text-green-700 dark:text-green-300 mb-2">Your Tags</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="(tag, idx) in tags" :key="tag">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-700 transition-colors duration-200">
                            <span x-text="tag"></span>
                            <button type="button" class="ml-2 text-green-700 hover:text-red-600 dark:text-green-300 dark:hover:text-red-400 focus:outline-none" @click="removeTag(idx)">
                                &times;
                            </button>
                        </span>
                    </template>
                    <input
                        x-model="input"
                        @keydown.enter.prevent="addTag()"
                        @keydown.tab.prevent="addTag()"
                        @keydown.",".prevent="addTag()"
                        type="text"
                        class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Add tag and press Enter, Tab, or Comma"
                    >
                </div>
                <input type="hidden" name="user_tags" :value="JSON.stringify(tags)">
                <span class="text-xs text-gray-400 mt-1 block">Press Enter, Tab, or comma to add. Click × to remove.</span>
            </div>

            <!-- Snippet Stats -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg transition-colors duration-200">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 transition-colors duration-200">Snippet Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 transition-colors duration-200">Created:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->created_at->format('M j, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 transition-colors duration-200">Last updated:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 transition-colors duration-200">{{ $snippet->updated_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <div class="flex space-x-3">
                    <a href="{{ route('snippets.show', $snippet) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors duration-200">
                        Cancel
                    </a>
                    @can('delete', $snippet)
                        <button type="button"
                                onclick="confirmDelete()"
                                class="px-4 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/50 hover:bg-red-100 dark:hover:bg-red-900/75 rounded-lg transition-colors duration-200">
                            Delete Snippet
                        </button>
                    @endcan
                </div>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
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

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this snippet? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>

<!-- Monaco Editor -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs/loader.js"></script>
@include('partials.monaco-theme-loader')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Alpine.js tag input component
function tagInput(initial) {
    return {
        tags: Array.isArray(initial.tags) ? initial.tags : (typeof initial.tags === 'string' && initial.tags ? JSON.parse(initial.tags) : []),
        input: initial.input || '',
        addTag() {
            let tag = this.input.trim();
            if (tag.endsWith(',')) tag = tag.slice(0, -1);
            if (tag && !this.tags.includes(tag)) {
                this.tags.push(tag);
            }
            this.input = '';
        },
        removeTag(idx) {
            this.tags.splice(idx, 1);
        }
    }
}

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

            monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: snippetContent,
                language: monacoLanguage,
                theme: themeName,
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
                formatOnType: true,
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

            // Update hidden textarea when editor content changes
            monacoEditor.onDidChangeModelContent(function() {
                document.getElementById('content').value = monacoEditor.getValue();
            });

            // Update language when dropdown changes
            document.getElementById('language').addEventListener('change', updateEditorLanguage);
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
