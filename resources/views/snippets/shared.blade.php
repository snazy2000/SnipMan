<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $snippet->title }} - Shared Snippet</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .monaco-container {
            height: 500px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        <h1 class="text-xl font-semibold text-gray-900">Shared Snippet</h1>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $share->views }} {{ Str::plural('view', $share->views) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Snippet Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $snippet->title }}</h2>
                            <div class="mt-2 flex items-center text-sm text-gray-600 space-x-4">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $snippet->creator->name }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $snippet->created_at->format('M j, Y') }}
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($snippet->language) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="copyToClipboard()"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy Code
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Code Editor -->
                <div class="p-6">
                    <div id="monaco-editor" class="monaco-container"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Powered by <span class="font-medium text-indigo-600">{{ config('app.name', 'Snippet Manager') }}</span></p>
            </div>
        </main>
    </div>

    <!-- Monaco Editor -->
    <script>
        // Only load Monaco if not already loaded
        if (typeof monaco === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs/loader.js';
            script.onload = initializeMonaco;
            document.head.appendChild(script);
        } else {
            initializeMonaco();
        }

        function initializeMonaco() {
            // Only configure require if not already configured
            if (typeof require === 'undefined' || !require.s || !require.s.contexts._ || !require.s.contexts._.config.paths.vs) {
                require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs' }});
            }

            require(['vs/editor/editor.main'], function() {
                const snippetContent = @json($snippet->content);
                const currentLanguage = '{{ $snippet->language }}';

                // Language mapping
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
                    'dart': 'dart',
                    'scala': 'scala',
                    'r': 'r',
                    'perl': 'perl',
                    'shell': 'shell',
                    'powershell': 'powershell',
                    'sql': 'sql',
                    'html': 'html',
                    'css': 'css',
                    'scss': 'scss',
                    'less': 'less',
                    'json': 'json',
                    'xml': 'xml',
                    'yaml': 'yaml',
                    'markdown': 'markdown'
                };

                const monacoLanguage = languageMap[currentLanguage] || 'plaintext';

                // Small delay to ensure container is properly sized
                setTimeout(() => {
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
                        selectOnLineNumbers: true,
                        scrollbar: {
                            alwaysConsumeMouseWheel: false,
                            handleMouseWheel: true
                        },
                        mouseWheelZoom: false,
                        fastScrollSensitivity: 5
                    });

                    // Force layout after creation
                    setTimeout(() => {
                        if (window.monacoEditor) {
                            window.monacoEditor.layout();
                        }
                    }, 100);
                }, 50);
            });
        }

        function copyToClipboard() {
            if (window.monacoEditor) {
                const code = window.monacoEditor.getValue();
                navigator.clipboard.writeText(code).then(() => {
                    // Show success message
                    const button = event.target.closest('button');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
                    button.classList.add('text-green-600', 'border-green-300');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('text-green-600', 'border-green-300');
                    }, 2000);
                }).catch(() => {
                    alert('Failed to copy code to clipboard');
                });
            }
        }
    </script>
</body>
</html>
