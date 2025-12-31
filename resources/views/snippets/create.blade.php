@extends('layouts.snippets')

@section('title', 'Create Snippet')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">Create New Snippet</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors duration-200">Save and organize your code snippets</p>
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

        <form action="{{ route('snippets.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Snippet Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
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
                        <option value="javascript" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                        <option value="typescript" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'typescript' ? 'selected' : '' }}>TypeScript</option>
                        <option value="python" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'python' ? 'selected' : '' }}>Python</option>
                        <option value="php" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'php' ? 'selected' : '' }}>PHP</option>
                        <option value="java" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'java' ? 'selected' : '' }}>Java</option>
                        <option value="csharp" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'csharp' ? 'selected' : '' }}>C#</option>
                        <option value="cpp" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'cpp' ? 'selected' : '' }}>C++</option>
                        <option value="c" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'c' ? 'selected' : '' }}>C</option>
                        <option value="go" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'go' ? 'selected' : '' }}>Go</option>
                        <option value="rust" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'rust' ? 'selected' : '' }}>Rust</option>
                        <option value="ruby" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'ruby' ? 'selected' : '' }}>Ruby</option>
                        <option value="swift" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'swift' ? 'selected' : '' }}>Swift</option>
                        <option value="kotlin" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'kotlin' ? 'selected' : '' }}>Kotlin</option>
                        <option value="html" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'html' ? 'selected' : '' }}>HTML</option>
                        <option value="css" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="sql" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'sql' ? 'selected' : '' }}>SQL</option>
                        <option value="bash" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'bash' ? 'selected' : '' }}>Bash</option>
                        <option value="powershell" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'powershell' ? 'selected' : '' }}>PowerShell</option>
                        <option value="json" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'json' ? 'selected' : '' }}>JSON</option>
                        <option value="yaml" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'yaml' ? 'selected' : '' }}>YAML</option>
                        <option value="xml" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'xml' ? 'selected' : '' }}>XML</option>
                        <option value="markdown" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'markdown' ? 'selected' : '' }}>Markdown</option>
                        <option value="other" {{ old('language', auth()->user()->monaco_language ?? 'javascript') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('language')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Owner Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Owner <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <!-- Personal Snippet -->
                        <label class="flex items-center">
                            <input type="radio"
                                   name="owner_type"
                                   value="personal"
                                   class="text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200"
                                   {{ old('owner_type', 'personal') == 'personal' ? 'checked' : '' }}
                                   onchange="updateOwnerOptions()">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 transition-colors duration-200">Personal Snippet</span>
                        </label>

                        <!-- Team Snippet -->
                        @if($teams->count() > 0)
                            <label class="flex items-center">
                                <input type="radio"
                                       name="owner_type"
                                       value="team"
                                       class="text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200"
                                       {{ old('owner_type') == 'team' ? 'checked' : '' }}
                                       onchange="updateOwnerOptions()">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 transition-colors duration-200">Team Snippet</span>
                            </label>
                        @else
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic transition-colors duration-200">
                                You don't have create permissions for any teams.
                            </div>
                        @endif
                    </div>
                    @error('owner_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Team Selection -->
                @if($teams->count() > 0)
                    <div id="team-selection" style="{{ old('owner_type') == 'team' ? '' : 'display: none;' }}">
                        <label for="team_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                            Select Team <span class="text-red-500">*</span>
                        </label>
                        <select id="team_id"
                                name="team_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('team_id') border-red-500 @enderror">
                            @if($teams->count() > 1)
                                <option value="">Choose a team...</option>
                            @endif
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}"
                                    {{ old('team_id') == $team->id || ($teams->count() == 1) ? 'selected' : '' }}>
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
            @if($personalFolders->count() > 0 || $teamFolders->count() > 0)
                <div class="mb-6">
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Folder <span class="text-red-500">*</span>
                    </label>
                    <select id="folder_id"
                            name="folder_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('folder_id') border-red-500 @enderror"
                            required
                            {{ old('owner_type') == 'team' && empty(old('team_id')) ? 'disabled' : '' }}>
                        <option value="">{{ old('owner_type') == 'team' ? 'Select a team first...' : 'Select a folder...' }}</option>

                        <!-- Personal Folders -->
                        @foreach($personalFolders as $folder)
                            <option value="{{ $folder->id }}"
                                    class="personal-folder"
                                    {{ old('folder_id', request('folder_id')) == $folder->id ? 'selected' : '' }}
                                    style="{{ old('owner_type') == 'team' ? 'display: none;' : '' }}">
                                {{ $folder->name }}
                            </option>
                            @foreach($folder->children as $child)
                                <option value="{{ $child->id }}"
                                        class="personal-folder"
                                        {{ old('folder_id', request('folder_id')) == $child->id ? 'selected' : '' }}
                                        style="{{ old('owner_type') == 'team' ? 'display: none;' : '' }}">
                                    └─ {{ $child->name }}
                                </option>
                                @foreach($child->children as $grandchild)
                                    <option value="{{ $grandchild->id }}"
                                            class="personal-folder"
                                            {{ old('folder_id', request('folder_id')) == $grandchild->id ? 'selected' : '' }}
                                            style="{{ old('owner_type') == 'team' ? 'display: none;' : '' }}">
                                        &nbsp;&nbsp;└─ {{ $grandchild->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        @endforeach

                        <!-- Team Folders -->
                        @foreach($teamFolders as $folder)
                            <option value="{{ $folder->id }}"
                                    class="team-folder"
                                    data-team-id="{{ $folder->owner_id }}"
                                    {{ old('folder_id', request('folder_id')) == $folder->id ? 'selected' : '' }}
                                    style="{{ old('owner_type') != 'team' ? 'display: none;' : '' }}">
                                {{ $folder->name }} ({{ $folder->team_name }})
                            </option>
                            @foreach($folder->children as $child)
                                <option value="{{ $child->id }}"
                                        class="team-folder"
                                        data-team-id="{{ $folder->owner_id }}"
                                        {{ old('folder_id', request('folder_id')) == $child->id ? 'selected' : '' }}
                                        style="{{ old('owner_type') != 'team' ? 'display: none;' : '' }}">
                                    └─ {{ $child->name }} ({{ $folder->team_name }})
                                </option>
                                @foreach($child->children as $grandchild)
                                    <option value="{{ $grandchild->id }}"
                                            class="team-folder"
                                            data-team-id="{{ $folder->owner_id }}"
                                            {{ old('folder_id', request('folder_id')) == $grandchild->id ? 'selected' : '' }}
                                            style="{{ old('owner_type') != 'team' ? 'display: none;' : '' }}">
                                        &nbsp;&nbsp;└─ {{ $grandchild->name }} ({{ $folder->team_name }})
                                    </option>
                                @endforeach
                            @endforeach
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
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                    Code <span class="text-red-500">*</span>
                </label>

                <!-- Monaco Editor Container -->
                <div class="monaco-container">
                    <div id="monaco-placeholder" class="monaco-placeholder">Start typing your code here...</div>
                    <div id="monaco-editor" style="height: 100%;"></div>
                </div>

                <!-- Hidden textarea for form submission -->
                <textarea id="content"
                          name="content"
                          class="hidden @error('content') border-red-500 @enderror"
                          required>{{ old('content') }}</textarea>

                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- User Tags (Alpine.js) -->
            <div class="mb-6" x-data="tagInput({
                tags: @json(old('user_tags', [])),
                input: ''
            })">
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

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <a href="{{ route('snippets.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    Create Snippet
                </button>
            </div>
        </form>
    </div>

@push('scripts')
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
    </script>
@endpush

<script>
let monacoEditor;

    function updateOwnerOptions() {
        var ownerType = document.querySelector('input[name="owner_type"]:checked').value;
        var teamSelection = document.getElementById('team-selection');
        var folderSelect = document.getElementById('folder_id');
        var personalFolderOptions = document.querySelectorAll('.personal-folder');
        var teamFolderOptions = document.querySelectorAll('.team-folder');

        // Show/hide team selection
        if (ownerType === 'team') {
            teamSelection.style.display = '';
            // Hide personal folder options
            personalFolderOptions.forEach(opt => opt.style.display = 'none');

            var selectedTeamId = document.getElementById('team_id').value;
            // Enable/disable folder dropdown based on team selection
            folderSelect.disabled = !selectedTeamId;
            folderSelect.querySelector('option[value=""]').textContent = selectedTeamId ? 'Select a folder...' : 'Select a team first...';

            // Show team folder options for the selected team only
            teamFolderOptions.forEach(opt => {
                if (selectedTeamId && opt.dataset.teamId === selectedTeamId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
        } else {
            teamSelection.style.display = 'none';
            // Show personal folder options
            personalFolderOptions.forEach(opt => opt.style.display = '');
            // Hide team folder options
            teamFolderOptions.forEach(opt => opt.style.display = 'none');
            // Enable folder select and update placeholder
            folderSelect.disabled = false;
            folderSelect.querySelector('option[value=""]').textContent = 'Select a folder...';
        }

        // Reset folder selection
        folderSelect.value = '';
    }

    // Add event listener for team selection change
    document.getElementById('team_id').addEventListener('change', function() {
        if (document.querySelector('input[name="owner_type"]:checked').value === 'team') {
            var selectedTeamId = this.value;
            var folderSelect = document.getElementById('folder_id');
            var teamFolderOptions = document.querySelectorAll('.team-folder');

            // Enable/disable folder dropdown based on team selection
            folderSelect.disabled = !selectedTeamId;
            folderSelect.querySelector('option[value=""]').textContent =
                selectedTeamId ? 'Select a folder...' : 'Select a team first...';

            // Show only folders for the selected team
            teamFolderOptions.forEach(opt => {
                if (selectedTeamId && opt.dataset.teamId === selectedTeamId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });

            // Reset folder selection
            folderSelect.value = '';
        }
    });

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
    // Wait for Monaco loader to be available
    function initializeMonaco() {
        if (typeof require === 'undefined') {
            setTimeout(initializeMonaco, 100);
            return;
        }

        require.config({
            paths: {
                'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs'
            }
        });

        require(['vs/editor/editor.main'], async function() {
            // Load user's preferred theme
            console.log('Starting theme load for:', userMonacoTheme);
            const themeName = await loadMonacoTheme(userMonacoTheme);
            console.log('Theme loaded, returned name:', themeName);

            monacoEditor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: '',
                language: userMonacoLanguage,
                theme: 'vs-dark', // Start with default theme
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

            console.log('Editor created, now setting theme:', themeName);
            // Wait a moment for editor to fully initialize, then set theme
            setTimeout(() => {
                monaco.editor.setTheme(themeName);
                console.log('Theme applied:', themeName);
            }, 100);

            // Set initial content if there's old input
            const oldContent = {{ Js::from(old('content', '')) }};
            if (oldContent) {
                monacoEditor.setValue(oldContent);
            }

            // Update hidden textarea when editor content changes
            monacoEditor.onDidChangeModelContent(function() {
                const content = monacoEditor.getValue();
                document.getElementById('content').value = content;

                // Toggle placeholder visibility
                const placeholder = document.getElementById('monaco-placeholder');
                if (content.trim().length > 0) {
                    placeholder.classList.add('hidden');
                } else {
                    placeholder.classList.remove('hidden');
                }
            });

            // Update language when dropdown changes
            document.getElementById('language').addEventListener('change', updateEditorLanguage);
        });
    }

    initializeMonaco();
});

// Update hidden textarea before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (monacoEditor) {
        document.getElementById('content').value = monacoEditor.getValue();
    }
});
</script>
@endsection
