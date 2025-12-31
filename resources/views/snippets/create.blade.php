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
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-200">
                        Folder (Optional)
                    </label>
                    <button type="button" id="create-folder-btn" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                        + Create Folder
                    </button>
                </div>

                <select id="folder_id"
                        name="folder_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('folder_id') border-red-500 @enderror"
                        {{ old('owner_type') == 'team' && empty(old('team_id')) ? 'disabled' : '' }}>
                    <option value="">{{ old('owner_type') == 'team' ? 'Select a team first...' : 'Select a folder...' }}</option>

                    <!-- Personal Folders -->
                    @include('partials.folder-options', [
                        'folders' => $personalFolders,
                        'level' => 0,
                        'selectedId' => old('folder_id', request('folder_id')),
                        'folderClass' => 'personal-folder',
                        'hideStyle' => old('owner_type') == 'team' ? 'display: none;' : ''
                    ])

                    <!-- Team Folders -->
                    @foreach($teamFolders as $rootFolder)
                        @include('partials.folder-options', [
                            'folders' => collect([$rootFolder]),
                            'level' => 0,
                            'selectedId' => old('folder_id', request('folder_id')),
                            'folderClass' => 'team-folder',
                            'teamId' => $rootFolder->owner_id,
                            'hideStyle' => old('owner_type') != 'team' ? 'display: none;' : ''
                        ])
                    @endforeach
                </select>

                <div id="no-folders-banner" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 transition-colors duration-200" style="display: none;">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>No folders available.</strong>
                        <button type="button" id="no-folders-create-link" class="text-blue-600 dark:text-blue-400 underline hover:no-underline font-medium">Create a folder</button>
                        to organize your snippets.
                    </p>
                </div>

                @error('folder_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

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

    <!-- Folder Creation Modal (Outside Form) -->
    <div id="folder-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 transition-colors duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Create New Folder</h3>
                <button type="button" onclick="closeFolderModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="folder-creation-form" onsubmit="createFolder(event)">
                <input type="hidden" id="modal-owner-type" name="owner_type" value="personal">
                <input type="hidden" id="modal-team-id" name="team_id" value="">

                <div class="mb-4">
                    <label for="modal-folder-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Folder Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="modal-folder-name"
                           name="name"
                           required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200"
                           placeholder="Enter folder name">
                </div>

                <div id="modal-parent-container" class="mb-4" style="display: none;">
                    <label for="modal-parent-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Parent Folder (Optional)
                    </label>
                    <select id="modal-parent-id"
                            name="parent_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200">
                        <option value="">No parent (root level)</option>
                    </select>
                </div>

                <div id="modal-error" class="mb-4 text-sm text-red-600 dark:text-red-400" style="display: none;"></div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeFolderModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            id="modal-submit-btn"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600 rounded-lg transition-colors duration-200">
                        Create Folder
                    </button>
                </div>
            </form>
        </div>
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
        var noFoldersBanner = document.getElementById('no-folders-banner');
        var noFoldersCreateLink = document.getElementById('no-folders-create-link');
        var defaultOption = folderSelect.querySelector('option[value=""]');

        // Show/hide team selection
        if (ownerType === 'team') {
            teamSelection.style.display = '';
            // Hide personal folder options
            personalFolderOptions.forEach(opt => opt.style.display = 'none');

            var teamIdElement = document.getElementById('team_id');
            var selectedTeamId = teamIdElement ? teamIdElement.value : null;
            // Enable/disable folder dropdown based on team selection
            folderSelect.disabled = !selectedTeamId;

            if (!selectedTeamId) {
                defaultOption.textContent = 'Select a team first...';
                folderSelect.style.display = '';
                noFoldersBanner.style.display = 'none';
            } else {
                // Show team folder options for the selected team only
                var hasVisibleFolders = false;
                teamFolderOptions.forEach(opt => {
                    if (opt.dataset.teamId === selectedTeamId) {
                        opt.style.display = '';
                        hasVisibleFolders = true;
                    } else {
                        opt.style.display = 'none';
                    }
                });

                // Show dropdown or banner based on folder availability
                if (hasVisibleFolders) {
                    defaultOption.textContent = 'Select a folder...';
                    folderSelect.style.display = '';
                    noFoldersBanner.style.display = 'none';
                } else {
                    folderSelect.style.display = 'none';
                    noFoldersBanner.style.display = 'block';
                }
            }
        } else {
            teamSelection.style.display = 'none';
            // Show personal folder options
            personalFolderOptions.forEach(opt => opt.style.display = '');
            // Hide team folder options
            teamFolderOptions.forEach(opt => opt.style.display = 'none');
            // Enable folder select and update placeholder
            folderSelect.disabled = false;

            // Check if there are personal folders
            var hasPersonalFolders = personalFolderOptions.length > 0;
            if (hasPersonalFolders) {
                defaultOption.textContent = 'Select a folder...';
                folderSelect.style.display = '';
                noFoldersBanner.style.display = 'none';
            } else {
                folderSelect.style.display = 'none';
                noFoldersBanner.style.display = 'block';
            }
        }

        // Reset folder selection
        folderSelect.value = '';
    }

    // Add event listener for team selection change
    var teamIdElement = document.getElementById('team_id');
    if (teamIdElement) {
        teamIdElement.addEventListener('change', function() {
            if (document.querySelector('input[name="owner_type"]:checked').value === 'team') {
                updateOwnerOptions();
            }
        });
    }

    // Modal functions
    var createFolderBtn = document.getElementById('create-folder-btn');
    if (createFolderBtn) {
        createFolderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openFolderModal();
        });
    }

    var noFoldersCreateLink = document.getElementById('no-folders-create-link');
    if (noFoldersCreateLink) {
        noFoldersCreateLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openFolderModal();
        });
    }

    function openFolderModal() {
        var ownerType = document.querySelector('input[name="owner_type"]:checked')?.value;
        var modal = document.getElementById('folder-modal');
        var modalOwnerType = document.getElementById('modal-owner-type');
        var modalTeamId = document.getElementById('modal-team-id');
        var modalParentContainer = document.getElementById('modal-parent-container');
        var modalParentSelect = document.getElementById('modal-parent-id');

        modalOwnerType.value = ownerType === 'team' ? 'team' : 'personal';

        // Clear and populate parent folder dropdown
        modalParentSelect.innerHTML = '<option value="">No parent (root level)</option>';

        if (ownerType === 'team') {
            var teamIdElement = document.getElementById('team_id');
            var teamId = teamIdElement ? teamIdElement.value : '';
            modalTeamId.value = teamId;

            // Get team folders for this team
            var teamFolderOptions = document.querySelectorAll('.team-folder');
            var hasFolders = false;

            teamFolderOptions.forEach(opt => {
                if (opt.dataset.teamId === teamId && opt.value) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.textContent;
                    modalParentSelect.appendChild(option);
                    hasFolders = true;
                }
            });

            modalParentContainer.style.display = hasFolders ? 'block' : 'none';
        } else {
            modalTeamId.value = '';

            // Get personal folders
            var personalFolderOptions = document.querySelectorAll('.personal-folder');
            var hasFolders = false;

            personalFolderOptions.forEach(opt => {
                if (opt.value) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.textContent;
                    modalParentSelect.appendChild(option);
                    hasFolders = true;
                }
            });

            modalParentContainer.style.display = hasFolders ? 'block' : 'none';
        }

        document.getElementById('modal-folder-name').value = '';
        document.getElementById('modal-error').style.display = 'none';
        modal.style.display = 'flex';
    }

    function closeFolderModal() {
        var modal = document.getElementById('folder-modal');
        modal.style.display = 'none';
    }

    async function createFolder(e) {
        e.preventDefault();

        var submitBtn = document.getElementById('modal-submit-btn');
        var errorDiv = document.getElementById('modal-error');
        var folderName = document.getElementById('modal-folder-name').value;
        var ownerType = document.getElementById('modal-owner-type').value;
        var teamId = document.getElementById('modal-team-id').value;
        var parentId = document.getElementById('modal-parent-id').value;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        errorDiv.style.display = 'none';

        try {
            var formData = new FormData();
            formData.append('name', folderName);
            formData.append('owner_type', ownerType);
            if (teamId) {
                formData.append('team_id', teamId);
            }
            if (parentId) {
                formData.append('parent_id', parentId);
            }

            var response = await fetch('{{ route("folders.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            var data = await response.json();

            if (response.ok) {
                // Add the new folder to the dropdown
                var folderSelect = document.getElementById('folder_id');
                var option = document.createElement('option');
                option.value = data.folder.id;

                // Add indentation if it's a child folder
                var folderName = data.folder.name;
                if (parentId) {
                    // Find the parent option to determine indentation level
                    var parentOption = folderSelect.querySelector('option[value="' + parentId + '"]');
                    if (parentOption) {
                        var parentText = parentOption.textContent;
                        if (parentText.startsWith('└─')) {
                            folderName = '  └─ ' + folderName; // Grandchild
                        } else if (parentText.startsWith('&nbsp;&nbsp;└─')) {
                            folderName = '    └─ ' + folderName; // Great-grandchild (max level)
                        } else {
                            folderName = '└─ ' + folderName; // Child
                        }
                    }
                }

                option.textContent = folderName;
                option.selected = true;

                if (ownerType === 'team') {
                    option.className = 'team-folder';
                    option.dataset.teamId = teamId;
                } else {
                    option.className = 'personal-folder';
                }

                folderSelect.appendChild(option);

                // Update UI
                folderSelect.style.display = '';
                document.getElementById('no-folders-banner').style.display = 'none';

                closeFolderModal();

                // Show success toast
                showToast('Folder "' + folderName + '" created successfully', 'success');
            } else {
                errorDiv.textContent = data.message || 'Failed to create folder';
                errorDiv.style.display = 'block';
            }
        } catch (error) {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Folder';
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
