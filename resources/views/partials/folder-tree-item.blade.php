@php
    $hasChildren = $folder->children->count() > 0;
    $hasSnippets = $folder->snippets->count() > 0;
    $isExpanded = true; // Default all folders to open
    $paddingLeft = $level * 16; // 16px per level
@endphp

<div class="folder-tree-item" data-folder-id="{{ $folder->id }}" data-level="{{ $level }}">
    <!-- Folder Header -->
    <div class="folder-header flex items-center rounded px-1 py-1"
         style="padding-left: {{ $paddingLeft }}px">

        <!-- Expand/Collapse Icon -->
        @if($hasChildren || $hasSnippets)
            <button class="folder-toggle-btn w-3 h-3 mr-1 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 flex items-center justify-center"
                    onclick="toggleFolder({{ $folder->id }}); event.stopPropagation();">
                <svg class="folder-toggle w-3 h-3 transition-transform {{ $isExpanded ? 'transform rotate-90' : '' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @else
            <div class="w-3 h-3 mr-1"></div>
        @endif

        <!-- Draggable Folder Area -->
        <div class="flex items-center flex-1 cursor-move hover:bg-gray-50 dark:hover:bg-gray-700 rounded px-1 drop-zone transition-colors duration-200"
             draggable="true"
             data-type="folder"
             data-folder-id="{{ $folder->id }}"
             data-drop-type="both"
             onmousedown="this.setAttribute('data-mousedown', Date.now())"
             onclick="if (!window.isDragging && (!this.getAttribute('data-mousedown') || Date.now() - this.getAttribute('data-mousedown') < 200)) { window.location.href='{{ route('folders.show', $folder) }}'; }">

            <!-- Folder Icon -->
            <svg class="w-4 h-4 mr-2 {{ $isTeam ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="pointer-events: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>

            <!-- Folder Name -->
            <div class="flex-1 min-w-0" style="pointer-events: none;">
                <span class="text-sm text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white truncate block transition-colors duration-200">
                    {{ $folder->name }}
                </span>
            </div>

            <!-- Snippet Count -->
            @if($hasSnippets)
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-1 rounded transition-colors duration-200" style="pointer-events: none;">{{ $folder->snippets->count() }}</span>
            @endif
        </div>
    </div>

    <!-- Folder Contents (Children and Snippets) -->
    <div class="folder-contents" id="folder-{{ $folder->id }}-contents" data-folder-id="{{ $folder->id }}">

        <!-- Child Folders -->
        @foreach($folder->children as $childFolder)
            @include('partials.folder-tree-item', ['folder' => $childFolder, 'level' => $level + 1, 'isTeam' => $isTeam])
        @endforeach

        <!-- Snippets in this Folder -->
        @foreach($folder->snippets as $snippet)
            <div class="snippet-item flex items-center px-2 py-1 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-move transition-colors duration-200"
                 style="margin-left: {{ $paddingLeft + 8 }}px;"
                 draggable="true"
                 data-snippet-id="{{ $snippet->id }}"
                 data-type="snippet"
                 onmousedown="this.setAttribute('data-mousedown', Date.now())"
                 onclick="if (!window.isDragging && (!this.getAttribute('data-mousedown') || Date.now() - this.getAttribute('data-mousedown') < 200)) { window.location.href='{{ route('snippets.show', $snippet) }}'; }">

                <div class="w-3 h-3 mr-1"></div> <!-- Spacer for alignment -->

                <!-- Snippet Icon -->
                <div class="w-4 h-4 mr-2 flex items-center justify-center"
                     style="pointer-events: none;">
                    @php
                        $lang = strtolower($snippet->language);
                    @endphp

                    @if($lang === 'javascript' || $lang === 'js')
                        <i class="fab fa-js-square text-yellow-500 text-sm"></i>
                    @elseif($lang === 'python' || $lang === 'py')
                        <i class="fab fa-python text-blue-500 text-sm"></i>
                    @elseif($lang === 'php')
                        <i class="fab fa-php text-purple-600 text-sm"></i>
                    @elseif($lang === 'java')
                        <i class="fab fa-java text-red-600 text-sm"></i>
                    @elseif($lang === 'html' || $lang === 'htm')
                        <i class="fab fa-html5 text-orange-600 text-sm"></i>
                    @elseif($lang === 'css')
                        <i class="fab fa-css3-alt text-blue-600 text-sm"></i>
                    @elseif($lang === 'typescript' || $lang === 'ts')
                        <i class="fab fa-js-square text-blue-600 text-sm"></i>
                    @elseif($lang === 'react' || $lang === 'jsx')
                        <i class="fab fa-react text-cyan-500 text-sm"></i>
                    @elseif($lang === 'vue' || $lang === 'vuejs')
                        <i class="fab fa-vuejs text-green-500 text-sm"></i>
                    @elseif($lang === 'node' || $lang === 'nodejs')
                        <i class="fab fa-node-js text-green-600 text-sm"></i>
                    @elseif($lang === 'git')
                        <i class="fab fa-git-alt text-orange-500 text-sm"></i>
                    @elseif($lang === 'docker')
                        <i class="fab fa-docker text-blue-500 text-sm"></i>
                    @elseif($lang === 'aws')
                        <i class="fab fa-aws text-orange-400 text-sm"></i>
                    @elseif($lang === 'sql' || $lang === 'mysql' || $lang === 'postgresql')
                        <i class="fas fa-database text-blue-700 text-sm"></i>
                    @elseif($lang === 'json')
                        <i class="fas fa-code text-yellow-600 text-sm"></i>
                    @elseif($lang === 'markdown' || $lang === 'md')
                        <i class="fab fa-markdown text-gray-700 text-sm"></i>
                    @elseif($lang === 'xml')
                        <i class="fas fa-file-code text-orange-500 text-sm"></i>
                    @elseif($lang === 'bash' || $lang === 'shell' || $lang === 'sh')
                        <i class="fas fa-terminal text-green-600 text-sm"></i>
                    @elseif($lang === 'c')
                        <i class="fas fa-code text-blue-600 text-sm"></i>
                    @elseif($lang === 'c++' || $lang === 'cpp')
                        <i class="fas fa-code text-blue-700 text-sm"></i>
                    @elseif($lang === 'c#' || $lang === 'csharp')
                        <i class="fas fa-code text-purple-600 text-sm"></i>
                    @elseif($lang === 'go' || $lang === 'golang')
                        <i class="fas fa-code text-cyan-600 text-sm"></i>
                    @elseif($lang === 'rust' || $lang === 'powershell')
                        <i class="fas fa-code text-orange-700 text-sm"></i>
                    @elseif($lang === 'swift')
                        <i class="fab fa-swift text-orange-500 text-sm"></i>
                    @elseif($lang === 'kotlin')
                        <i class="fas fa-code text-purple-500 text-sm"></i>
                    @elseif($lang === 'ruby')
                        <i class="fas fa-gem text-red-600 text-sm"></i>
                    @elseif($lang === 'laravel')
                        <i class="fab fa-laravel text-red-500 text-sm"></i>
                    @elseif($lang === 'sass' || $lang === 'scss')
                        <i class="fab fa-sass text-pink-500 text-sm"></i>
                    @elseif($lang === 'less')
                        <i class="fab fa-less text-blue-600 text-sm"></i>
                    @elseif($lang === 'bootstrap')
                        <i class="fab fa-bootstrap text-purple-600 text-sm"></i>
                    @elseif($lang === 'angular')
                        <i class="fab fa-angular text-red-600 text-sm"></i>
                    @elseif($lang === 'npm')
                        <i class="fab fa-npm text-red-500 text-sm"></i>
                    @elseif($lang === 'yarn')
                        <i class="fab fa-yarn text-blue-500 text-sm"></i>
                    @elseif($lang === 'webpack')
                        <i class="fab fa-webpack text-blue-400 text-sm"></i>
                    @else
                        <!-- Default fallback icon with language abbreviation -->
                        <div class="w-4 h-4 text-xs bg-gray-200 rounded flex items-center justify-center text-gray-600 font-medium">
                            {{ strtoupper(substr($snippet->language, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <!-- Snippet Name -->
                <span class="flex-1 truncate hover:text-gray-900 dark:hover:text-white transition-colors duration-200" style="pointer-events: none;">
                    {{ $snippet->title }}
                </span>
            </div>
        @endforeach

        <!-- Empty State for Folder -->
        @if(!$hasChildren && !$hasSnippets)
            <div class="text-xs text-gray-400 dark:text-gray-500 px-2 py-1" style="margin-left: {{ ($level + 1) * 12 }}px">
                Empty folder
            </div>
        @endif
    </div>
</div>
