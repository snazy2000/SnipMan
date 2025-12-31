<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Snippet Manager')) - {{ config('app.name', 'Snippet Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Alpine.js Cloak Style -->
        <style>
            [x-cloak] { display: none !important; }

            /* Toast Notifications */
            .toast-container {
                position: fixed;
                top: 5rem;
                right: 1.5rem;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                max-width: 24rem;
            }

            .toast {
                padding: 1rem 1.25rem;
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                display: flex;
                align-items: start;
                gap: 0.75rem;
                animation: slideIn 0.3s ease-out;
            }

            .toast.toast-error {
                background-color: #fef2f2;
                border-left: 4px solid #ef4444;
                color: #991b1b;
            }

            .dark .toast.toast-error {
                background-color: #7f1d1d;
                border-left-color: #dc2626;
                color: #fecaca;
            }

            .toast.toast-success {
                background-color: #f0fdf4;
                border-left: 4px solid #22c55e;
                color: #14532d;
            }

            .dark .toast.toast-success {
                background-color: #14532d;
                border-left-color: #16a34a;
                color: #bbf7d0;
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            .toast.removing {
                animation: slideOut 0.3s ease-in forwards;
            }
        </style>

        <!-- Dark Mode Script -->
        <script>
            // Check for saved theme preference or default to 'light' mode
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        @stack('styles')

        <!-- Tree View and Drag & Drop Styles -->
        <style>
            /* Custom Scrollbar for Sidebar */
            #sidebar-tree::-webkit-scrollbar {
                width: 8px;
            }

            #sidebar-tree::-webkit-scrollbar-track {
                background: transparent;
            }

            #sidebar-tree::-webkit-scrollbar-thumb {
                background: #9ca3af;
                border-radius: 4px;
            }

            #sidebar-tree::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }

            .dark #sidebar-tree::-webkit-scrollbar-thumb {
                background: #4b5563;
            }

            .dark #sidebar-tree::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }

            /* Firefox */
            #sidebar-tree {
                scrollbar-width: thin;
                scrollbar-color: #9ca3af transparent;
            }

            .dark #sidebar-tree {
                scrollbar-color: #4b5563 transparent;
            }

            /* Custom Scrollbar for Main Content */
            .main-content::-webkit-scrollbar {
                width: 10px;
            }

            .main-content::-webkit-scrollbar-track {
                background: transparent;
            }

            .main-content::-webkit-scrollbar-thumb {
                background: #9ca3af;
                border-radius: 5px;
            }

            .main-content::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }

            .dark .main-content::-webkit-scrollbar-thumb {
                background: #4b5563;
            }

            .dark .main-content::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }

            /* Firefox */
            .main-content {
                scrollbar-width: thin;
                scrollbar-color: #9ca3af transparent;
            }

            .dark .main-content {
                scrollbar-color: #4b5563 transparent;
            }

            .tree-container {
                user-select: none;
            }

            .folder-tree-item {
                position: relative;
            }

            .folder-header:hover {
                background-color: #f9fafb;
            }

            .dark .folder-header:hover {
                background-color: #374151;
            }

            .folder-contents {
                overflow: hidden;
                transition: all 0.1s ease-in-out;
            }

            .drop-zone {
                min-height: 20px;
                transition: all 0.1s ease-in-out;
                pointer-events: auto !important;
                position: relative;
            }

            /* Show drop zones when dragging */
            body.dragging .drop-zone {
                border-color: #3b82f6 !important;
                background-color: rgba(59, 130, 246, 0.1) !important;
                border-style: dashed !important;
                border-width: 2px !important;
            }

            .drop-zone.drag-over {
                border-color: #3b82f6 !important;
                background-color: rgba(59, 130, 246, 0.2) !important;
                border-style: solid !important;
            }

            .dragging {
                opacity: 0.5;
            }

            .drop-zone.drop-zone-disabled:not(.folder-header) {
                border-color: transparent !important;
                background-color: transparent !important;
                min-height: 0 !important;
                padding: 0 !important;
            }

            /* Show drop zones more clearly when dragging */
            body.dragging .drop-zone {
                border-color: #3b82f6 !important;
                background-color: rgba(59, 130, 246, 0.1) !important;
                border-style: dashed !important;
                border-width: 2px !important;
            }

            .drag-ghost {
                position: fixed;
                pointer-events: none;
                z-index: 1000;
                background: white;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .snippet-item:hover {
                background-color: #f3f4f6;
            }

            .folder-toggle {
                transition: transform 0.1s ease-in-out;
            }



        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-200"
          x-data="{ sidebarOpen: window.innerWidth >= 768 }"
          @resize.window="if (window.innerWidth >= 768) sidebarOpen = true">
        <!-- Toast Notification Container -->
        <div id="toast-container" class="toast-container"></div>

        <div class="min-h-screen">
            <!-- Top Navigation Bar -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 fixed w-full top-0 z-50 transition-colors duration-200">
                <div class="px-3 md:px-6 lg:px-8">
                    <div class="flex justify-between h-14 md:h-16">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 mr-3 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>

                            <div class="flex items-center">
                                <svg class="w-6 h-6 md:w-7 md:h-7 mr-2 md:mr-3 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                                <h1 class="hidden lg:block text-base md:text-xl font-semibold text-gray-900 dark:text-white transition-colors duration-200 whitespace-nowrap">{{ config('app.name', 'Snippet Manager') }}</h1>
                            </div>

                            <!-- Main Navigation Links -->
                            <div class="hidden md:flex items-center ml-4 lg:ml-8 space-x-3 lg:space-x-6">
                                <a href="{{ route('snippets.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 lg:px-3 py-2 text-sm font-medium transition-colors duration-200 whitespace-nowrap {{ request()->routeIs('snippets.index') ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400' : '' }}">
                                    Snippets
                                </a>
                                <a href="{{ route('snippets.sharedList') }}" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 lg:px-3 py-2 text-sm font-medium transition-colors duration-200 whitespace-nowrap {{ request()->routeIs('snippets.sharedList') ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400' : '' }}">
                                    Shared
                                </a>
                                <a href="{{ route('folders.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 lg:px-3 py-2 text-sm font-medium transition-colors duration-200 whitespace-nowrap {{ request()->routeIs('folders.*') ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400' : '' }}">
                                    Folders
                                </a>
                                <a href="{{ route('teams.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 lg:px-3 py-2 text-sm font-medium transition-colors duration-200 whitespace-nowrap {{ request()->routeIs('teams.*') ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400' : '' }}">
                                    Teams
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                    <a href="{{ route('admin.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 lg:px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center whitespace-nowrap {{ request()->routeIs('admin.*') ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400' : '' }}">
                                        <i class="fas fa-shield-alt mr-1.5 text-xs"></i><span>Admin</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 md:space-x-3 lg:space-x-4 flex-shrink-0">
                            <!-- Search -->
                            <div class="relative" x-data="searchComponent()">
                                <!-- Search Button (All Screens) -->
                                <button @click="showResults = true; $nextTick(() => $refs.searchInput.focus())"
                                        class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex-shrink-0"
                                        title="Search snippets, folders">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>

                                <!-- Search Modal (All Screens) -->
                                <div x-show="showResults"
                                     x-cloak
                                     @click.away="hideResults"
                                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-4"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0">
                                    <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-11/12 max-w-2xl mx-auto transition-colors duration-200">
                                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
                                            <div class="relative">
                                                <input type="text"
                                                       x-ref="searchInput"
                                                       placeholder="Search snippets, folders"
                                                       x-model="searchQuery"
                                                       @input="search"
                                                       @keydown.escape="hideResults"
                                                       class="w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-200">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                                <button @click="hideResults" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="max-h-96 overflow-y-auto">
                                            <!-- Loading State -->
                                            <div x-show="loading" x-cloak class="p-4 text-center">
                                                <div class="inline-flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Searching...</span>
                                                </div>
                                            </div>

                                            <!-- Results -->
                                            <div x-show="!loading" x-cloak>
                                                <!-- Snippets Results -->
                                                <template x-if="results.snippets && results.snippets.length > 0">
                                                    <div class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                                            Snippets
                                                        </div>
                                                        <template x-for="snippet in results.snippets" :key="snippet.id">
                                                            <a :href="`/snippets/${snippet.id}`"
                                                               @click="hideResults"
                                                               class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                                                                <div class="flex items-start justify-between">
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="snippet.title"></p>
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1" x-text="snippet.description || 'No description'"></p>
                                                                    </div>
                                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                                          x-text="snippet.language"></span>
                                                                </div>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Folders Results -->
                                                <template x-if="results.folders && results.folders.length > 0">
                                                    <div>
                                                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                                            Folders
                                                        </div>
                                                        <template x-for="folder in results.folders" :key="folder.id">
                                                            <a :href="`/folders/${folder.id}`"
                                                               @click="hideResults"
                                                               class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                                                                <div class="flex items-center">
                                                                    <svg class="w-4 h-4 mr-2 text-yellow-500 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                                                    </svg>
                                                                    <span class="text-sm text-gray-900 dark:text-white" x-text="folder.name"></span>
                                                                </div>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- No Results -->
                                                <div x-show="searchQuery.length > 1 && (!results.snippets || results.snippets.length === 0) && (!results.folders || results.folders.length === 0)"
                                                     x-cloak
                                                     class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No results found for "<span x-text="searchQuery"></span>"
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <div x-data="{
                                darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                                toggle() {
                                    this.darkMode = !this.darkMode;
                                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                                    if (this.darkMode) {
                                        document.documentElement.classList.add('dark');
                                    } else {
                                        document.documentElement.classList.remove('dark');
                                    }
                                }
                            }">
                                <button @click="toggle()"
                                        class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 w-10 h-10 flex items-center justify-center"
                                        :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                                    <div x-cloak>
                                        <template x-if="!darkMode">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="darkMode">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                        </template>
                                    </div>
                                </button>
                            </div>

                            <!-- NEW SNIPPET Button -->
                            <a href="{{ route('snippets.create') }}" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-3 lg:px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center transition-colors duration-200 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 md:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="hidden md:inline">NEW SNIPPET</span>
                            </a>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600 dark:text-indigo-300">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="hidden md:inline ml-2 text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</span>
                                    <svg class="ml-1 w-4 h-4 text-gray-400 dark:text-gray-500 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transition-colors duration-200">
                                    <div class="py-1">
                                        <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-600 transition-colors duration-200">
                                            <div class="font-medium">{{ Auth::user()->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ Auth::user()->email }}</div>
                                        </div>
                                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">Profile</a>
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">Dashboard</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="flex h-screen pt-14 md:pt-16"> <!-- pt-14 on mobile, pt-16 on desktop to account for top navigation -->
                <!-- Mobile sidebar backdrop -->
                <div x-show="sidebarOpen && window.innerWidth < 768"
                     x-cloak
                     @click="sidebarOpen = false"
                     class="fixed inset-0 bg-black bg-opacity-50 z-20 md:hidden"
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>

                <!-- Resizable Sidebar -->
                <div id="sidebar"
                     x-show="sidebarOpen || window.innerWidth >= 768"
                     class="bg-gray-50 dark:bg-gray-900 border-r-2 border-gray-300 dark:border-gray-600 flex flex-col transition-colors duration-200 relative"
                     :class="window.innerWidth < 768 ? 'fixed inset-y-0 left-0 z-30' : ''"
                     style="width: 280px; min-width: 200px;"
                     :style="window.innerWidth < 768 ? 'max-width: 80vw' : 'max-width: 600px'">
                    <!-- Folders Section -->
                    <div class="flex-1 overflow-y-auto p-4" id="sidebar-tree">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">FOLDERS</h3>
                            <button class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors" title="Add Folder">
                                <a href="{{ route('folders.create') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </a>
                            </button>
                        </div>

                        <!-- Personal Folders Tree -->
                        @php
                            $personalFolders = Auth::user()->folders()->whereNull('parent_id')->with(['children.children', 'snippets'])->get();
                        @endphp

                        <div class="personal-folders">
                            @if($personalFolders->isEmpty())
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No folders yet</p>
                                    <a href="{{ route('folders.create') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Create your first folder</a>
                                </div>
                            @else
                                <div class="tree-container">
                                    @foreach($personalFolders as $folder)
                                        @include('partials.folder-tree-item', ['folder' => $folder, 'level' => 0, 'isTeam' => false])
                                    @endforeach
                                </div>
                            @endif

                            {{-- Unfolderd Personal Snippets --}}
                            @php
                                $unfolderedPersonal = Auth::user()->snippets()
                                    ->where('owner_type', 'App\Models\User')
                                    ->whereNull('folder_id')
                                    ->latest()
                                    ->get();
                            @endphp
                            @if($unfolderedPersonal->isNotEmpty())
                                <div class="drop-zone rounded-lg p-2 min-h-[40px] transition-colors mt-2"
                                     data-folder-id="null" data-drop-type="snippets">
                                    @foreach($unfolderedPersonal as $snippet)
                                        <div class="snippet-item mb-1 cursor-move flex items-center px-2 py-1 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors duration-200"
                                             draggable="true"
                                             data-snippet-id="{{ $snippet->id }}"
                                             data-type="snippet">
                                            <div class="w-4 h-4 mr-2 text-xs bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-gray-600 dark:text-gray-300"
                                                 draggable="false">
                                                {{ strtoupper(substr($snippet->language, 0, 2)) }}
                                            </div>
                                            <span class="truncate cursor-pointer hover:text-gray-900 dark:hover:text-white transition-colors duration-200"
                                                  onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                                                {{ $snippet->title }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Team Folders Tree -->
                        @if(Auth::user()->teams->isNotEmpty())
                        <div class="team-folders mt-6">
                            <h3 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">TEAM FOLDERS</h3>
                                @foreach(Auth::user()->teams as $team)
                                    @php
                                        $teamFolders = $team->folders()->whereNull('parent_id')->with(['children.children', 'snippets'])->get();
                                    @endphp
                                    <div class="mb-4" data-team-id="{{ $team->id }}">
                                        <div class="flex items-center justify-between mb-2 pl-2 pr-1">
                                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $team->name }}</h4>
                                            @if(in_array($team->pivot->role, ['owner', 'editor']))
                                                <a href="{{ route('folders.create', ['team_id' => $team->id]) }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300" title="Add Folder to {{ $team->name }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                        @if($teamFolders->isNotEmpty())
                                            <div class="tree-container">
                                                @foreach($teamFolders as $folder)
                                                    @php
                                                        $folder->team_id = $team->id;
                                                    @endphp
                                                    @include('partials.folder-tree-item', ['folder' => $folder, 'level' => 0, 'isTeam' => true])
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Unfolderd Team Snippets --}}
                                        @php
                                            $unfolderedTeam = $team->snippets()
                                                ->whereNull('folder_id')
                                                ->latest()
                                                ->get();
                                        @endphp
                                        @if($unfolderedTeam->isNotEmpty())
                                            <div class="drop-zone rounded-lg p-2 min-h-[40px] transition-colors mt-2"
                                                 data-folder-id="null" data-drop-type="snippets" data-team-id="{{ $team->id }}">
                                                @foreach($unfolderedTeam as $snippet)
                                                    <div class="snippet-item mb-1 cursor-move flex items-center px-2 py-1 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors duration-200"
                                                         draggable="true"
                                                         data-snippet-id="{{ $snippet->id }}"
                                                         data-type="snippet">
                                                        <div class="w-4 h-4 mr-2 text-xs bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-gray-600 dark:text-gray-300"
                                                             draggable="false">
                                                            {{ strtoupper(substr($snippet->language, 0, 2)) }}
                                                        </div>
                                                        <span class="truncate cursor-pointer hover:text-gray-900 dark:hover:text-white transition-colors duration-200"
                                                              onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                                                            {{ $snippet->title }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Resize Handle (Desktop Only) -->
                    <div id="sidebar-resize-handle"
                         class="hidden md:block absolute top-0 -right-1 w-2 h-full cursor-ew-resize hover:bg-indigo-400/30 transition-colors group z-10"
                         title="Drag to resize">
                        <div class="absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2 w-1 h-20 bg-gray-400 dark:bg-gray-500 group-hover:bg-indigo-500 group-hover:w-1.5 rounded-full transition-all"></div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-900 transition-colors duration-200 main-content">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 dark:border-green-500 p-4" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400 dark:text-green-300" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 dark:text-green-200">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 dark:border-red-500 p-4" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400 dark:text-red-300" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 dark:text-red-200">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-white dark:bg-gray-800 transition-colors duration-200">
                        <div class="h-full p-6">
                            @yield('content')
                        </div>
                    </main>
                </div>
            </div>
        </div>

        @stack('scripts')

        <script>
            // Tree View Functions
            function toggleFolder(folderId) {
                const contents = document.getElementById(`folder-${folderId}-contents`);
                const toggle = contents.parentElement.querySelector('.folder-toggle');

                const isCurrentlyOpen = !contents.classList.contains('hidden');

                if (isCurrentlyOpen) {
                    contents.classList.add('hidden');
                    toggle.classList.remove('transform', 'rotate-90');
                    saveFolderState(folderId, false);
                } else {
                    contents.classList.remove('hidden');
                    toggle.classList.add('transform', 'rotate-90');
                    saveFolderState(folderId, true);
                }
            }

            function saveFolderState(folderId, isOpen) {
                const folderStates = JSON.parse(localStorage.getItem('folderStates') || '{}');
                folderStates[folderId] = isOpen;
                localStorage.setItem('folderStates', JSON.stringify(folderStates));
            }

            function loadFolderStates() {
                const folderStates = JSON.parse(localStorage.getItem('folderStates') || '{}');

                // Apply saved states or default to open
                document.querySelectorAll('.folder-contents').forEach(contents => {
                    const folderId = contents.dataset.folderId;
                    const toggle = contents.parentElement.querySelector('.folder-toggle');

                    // Default to open if no saved state
                    const shouldBeOpen = folderStates.hasOwnProperty(folderId) ? folderStates[folderId] : true;

                    if (shouldBeOpen) {
                        contents.classList.remove('hidden');
                        if (toggle) {
                            toggle.classList.add('transform', 'rotate-90');
                        }
                    } else {
                        contents.classList.add('hidden');
                        if (toggle) {
                            toggle.classList.remove('transform', 'rotate-90');
                        }
                    }
                });
            }

            // Drag and Drop Variables
            let draggedElement = null;
            let draggedType = null;
            let draggedId = null;
            let dragGhost = null;

            // Initialize Drag and Drop
            document.addEventListener('DOMContentLoaded', function() {
                // Load folder states first
                loadFolderStates();

                // Wait a bit for folder states to apply, then initialize drag and drop
                setTimeout(() => {
                    initializeDragAndDrop();
                }, 200);

                // Add cleanup listeners
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && draggedElement) {
                        console.log('Escape pressed - cleaning up drag');
                        cleanupDrag();
                    }
                });

                // Only cleanup on mouse up if we're really stuck (no dragover events)
                let dragOverDetected = false;
                document.addEventListener('dragover', function(e) {
                    dragOverDetected = true;
                    console.log('Global dragover detected on:', e.target.tagName, e.target.className);
                });

                document.addEventListener('mouseup', function() {
                    if (draggedElement && !dragOverDetected) {
                        console.log('Mouse up without dragover - cleaning drag');
                        setTimeout(cleanupDrag, 100);
                    }
                    dragOverDetected = false;
                });
            });

            let dragInitialized = false;

            function initializeDragAndDrop() {
                if (dragInitialized) {
                    return;
                }

                const draggableItems = document.querySelectorAll('[draggable="true"]');
                const dropZones = document.querySelectorAll('.drop-zone');

                // Add drag listeners to all draggable items
                draggableItems.forEach(item => {
                    item.addEventListener('dragstart', handleDragStart);
                    item.addEventListener('dragend', handleDragEnd);
                    item.addEventListener('mousedown', function(e) {
                        // Don't prevent default, but mark that we might be dragging
                        this._mouseDownTime = Date.now();
                    });
                    item.addEventListener('click', function(e) {
                        // If this click happened very soon after mousedown and we're dragging, prevent it
                        if (window.isDragging || (this._mouseDownTime && Date.now() - this._mouseDownTime < 300)) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                    item.style.userSelect = 'none';
                });

                // Add drop listeners to all drop zones
                dropZones.forEach(zone => {
                    zone.addEventListener('dragover', handleDragOver);
                    zone.addEventListener('dragenter', handleDragEnter);
                    zone.addEventListener('dragleave', handleDragLeave);
                    zone.addEventListener('drop', handleDrop);
                });

                dragInitialized = true;
            }

            function handleDragStart(e) {
                console.log('Drag starting...');
                e.stopPropagation(); // Prevent event bubbling

                window.isDragging = true;
                draggedElement = e.target.closest('[draggable="true"]');
                if (!draggedElement) return;

                draggedType = draggedElement.dataset.type;
                draggedId = draggedElement.dataset.snippetId || draggedElement.dataset.folderId;

                console.log('Dragging:', draggedType, draggedId);

                // Simple visual feedback
                draggedElement.style.opacity = '0.5';
                document.body.classList.add('dragging');

                // Determine if dragged item is from team or personal section
                const draggedIsTeam = draggedElement.closest('.team-folders') !== null;
                const sourceTeamId = draggedIsTeam ? getTeamId(draggedElement) : null;

                // Show only valid drop zones
                document.querySelectorAll('.drop-zone').forEach(zone => {
                    const dropIsTeam = zone.closest('.team-folders') !== null;
                    const targetTeamId = dropIsTeam ? getTeamId(zone) : null;

                    let isValidDropZone = false;

                    if (draggedIsTeam && dropIsTeam) {
                        // For team items, team IDs must match
                        isValidDropZone = sourceTeamId === targetTeamId;
                    } else if (!draggedIsTeam && !dropIsTeam) {
                        // For personal items, both must be personal
                        isValidDropZone = true;
                    }

                    if (isValidDropZone) {
                        zone.classList.add('drop-zone-active');
                        zone.classList.remove('drop-zone-disabled');
                    } else {
                        zone.classList.remove('drop-zone-active');
                        zone.classList.add('drop-zone-disabled');
                    }
                });

                // Simple drag data - try multiple data formats for better compatibility
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedId);
                e.dataTransfer.setData('application/json', JSON.stringify({
                    type: draggedType,
                    id: draggedId
                }));

                // Fallback cleanup after 10 seconds in case dragend doesn't fire
                setTimeout(() => {
                    if (draggedElement) {
                        console.log('Timeout cleanup triggered');
                        cleanupDrag();
                    }
                }, 10000);
            }

            // Manual cleanup function in case dragend doesn't fire
            function cleanupDrag() {
                console.log('Manual cleanup triggered');
                if (draggedElement) {
                    draggedElement.style.opacity = '';
                }
                document.body.classList.remove('dragging');
                document.querySelectorAll('.drop-zone').forEach(zone => {
                    zone.classList.remove('drag-over');
                    zone.classList.remove('drop-zone-active');
                    zone.classList.remove('drop-zone-disabled');
                });
                draggedElement = null;
                draggedType = null;
                draggedId = null;

                // Reset dragging flag after a short delay to prevent click events
                setTimeout(() => {
                    window.isDragging = false;
                }, 100);
            }

            function handleDragEnd(e) {
                console.log('Drag end triggered - cleaning up');
                cleanupDrag();
                console.log('Cleanup complete');
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                console.log('Drag over:', e.target);
            }

            function handleDragEnter(e) {
                e.preventDefault();
                console.log('Drag entering:', e.target);
                const dropZone = e.target.closest('.drop-zone');
                if (dropZone && canDrop(dropZone)) {
                    console.log('Valid drop zone found:', dropZone.dataset.folderId);
                    dropZone.classList.add('drag-over');
                }
            }

            function handleDragLeave(e) {
                const dropZone = e.target.closest('.drop-zone');
                if (dropZone && !dropZone.contains(e.relatedTarget)) {
                    dropZone.classList.remove('drag-over');
                }
            }

            function handleDrop(e) {
                e.preventDefault();
                console.log('Drop event triggered!');
                const dropZone = e.target.closest('.drop-zone');

                console.log('Drop zone:', dropZone);
                console.log('Can drop?', dropZone ? canDrop(dropZone) : 'No drop zone');

                if (!dropZone || !canDrop(dropZone)) {
                    console.log('Drop rejected');
                    return;
                }

                dropZone.classList.remove('drag-over');

                const targetFolderId = dropZone.dataset.folderId;
                console.log('Moving to folder:', targetFolderId);

                // Perform the move operation
                if (draggedType === 'snippet') {
                    console.log('Moving snippet:', draggedId, 'to folder:', targetFolderId);
                    moveSnippet(draggedId, targetFolderId);
                } else if (draggedType === 'folder') {
                    console.log('Moving folder:', draggedId, 'to parent:', targetFolderId);
                    moveFolder(draggedId, targetFolderId);
                }
            }

            function getTeamId(element) {
                // Walk up the DOM tree to find the closest team section
                const teamSection = element.closest('[data-team-id]');
                return teamSection ? teamSection.dataset.teamId : null;
            }

            function canDrop(dropZone) {
                const dropType = dropZone.dataset.dropType;
                const targetFolderId = dropZone.dataset.folderId;

                // Prevent dropping on self
                if (draggedType === 'folder' && draggedId === targetFolderId) {
                    return false;
                }

                // Get the dragged element's owner type
                const draggedElement = document.querySelector(`[data-${draggedType}-id="${draggedId}"]`);
                const draggedIsTeam = draggedElement.closest('.team-folders') !== null;

                // Get the drop zone's owner type
                const dropIsTeam = dropZone.closest('.team-folders') !== null;

                // Prevent dropping between personal and team sections
                if (draggedIsTeam !== dropIsTeam) {
                    return false;
                }

                // If this is a team item, ensure it stays within the same team
                if (draggedIsTeam) {
                    const sourceTeamId = getTeamId(draggedElement);
                    const targetTeamId = getTeamId(dropZone);

                    if (sourceTeamId !== targetTeamId) {
                        return false;
                    }
                }

                // Check drop type compatibility
                if (draggedType === 'snippet' && (dropType === 'snippets' || dropType === 'both')) {
                    return true;
                }

                if (draggedType === 'folder' && (dropType === 'folders' || dropType === 'both')) {
                    return true;
                }

                return false;
            }

            function createDragGhost(e) {
                dragGhost = document.createElement('div');
                dragGhost.className = 'drag-ghost';

                if (draggedType === 'snippet') {
                    const title = draggedElement.querySelector('a').textContent.trim();
                    dragGhost.innerHTML = `📄 ${title}`;
                } else if (draggedType === 'folder') {
                    const name = draggedElement.querySelector('a').textContent.trim();
                    dragGhost.innerHTML = `📁 ${name}`;
                }

                dragGhost.style.left = (e.clientX + 10) + 'px';
                dragGhost.style.top = (e.clientY + 10) + 'px';

                document.body.appendChild(dragGhost);
            }

            function moveSnippet(snippetId, targetFolderId) {
                // Convert 'null' string to actual null
                const folderId = targetFolderId === 'null' ? null : targetFolderId;

                fetch(`/snippets/${snippetId}/move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        folder_id: folderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to update the sidebar
                        window.location.reload();
                    } else {
                        showToast('Failed to move snippet: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while moving the snippet.', 'error');
                });
            }

            function moveFolder(folderId, targetParentId) {
                // Convert 'null' string to actual null
                const parentId = targetParentId === 'null' ? null : targetParentId;

                fetch(`/folders/${folderId}/move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        parent_id: parentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to update the sidebar
                        window.location.reload();
                    } else {
                        showToast('Failed to move folder: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while moving the folder.', 'error');
                });
            }
        </script>

        <script>
            // Toast Notification System
            function showToast(message, type = 'error') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;

                const icon = type === 'error'
                    ? '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                    : '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';

                toast.innerHTML = `
                    ${icon}
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                `;

                container.appendChild(toast);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    toast.classList.add('removing');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            }

            function searchComponent() {
                return {
                    searchQuery: '',
                    results: { snippets: [], folders: [] },
                    loading: false,
                    showResults: false,
                    searchTimeout: null,

                    getLanguageIcon(language) {
                        const lang = language.toLowerCase();

                        if (lang === 'javascript' || lang === 'js') {
                            return '<i class="fab fa-js-square text-yellow-500 text-sm"></i>';
                        } else if (lang === 'python' || lang === 'py') {
                            return '<i class="fab fa-python text-blue-500 text-sm"></i>';
                        } else if (lang === 'php') {
                            return '<i class="fab fa-php text-purple-600 text-sm"></i>';
                        } else if (lang === 'java') {
                            return '<i class="fab fa-java text-red-600 text-sm"></i>';
                        } else if (lang === 'html' || lang === 'htm') {
                            return '<i class="fab fa-html5 text-orange-600 text-sm"></i>';
                        } else if (lang === 'css') {
                            return '<i class="fab fa-css3-alt text-blue-600 text-sm"></i>';
                        } else if (lang === 'typescript' || lang === 'ts') {
                            return '<i class="fab fa-js-square text-blue-600 text-sm"></i>';
                        } else if (lang === 'react' || lang === 'jsx') {
                            return '<i class="fab fa-react text-cyan-500 text-sm"></i>';
                        } else if (lang === 'vue' || lang === 'vuejs') {
                            return '<i class="fab fa-vuejs text-green-500 text-sm"></i>';
                        } else if (lang === 'node' || lang === 'nodejs') {
                            return '<i class="fab fa-node-js text-green-600 text-sm"></i>';
                        } else if (lang === 'git') {
                            return '<i class="fab fa-git-alt text-orange-500 text-sm"></i>';
                        } else if (lang === 'docker') {
                            return '<i class="fab fa-docker text-blue-500 text-sm"></i>';
                        } else if (lang === 'aws') {
                            return '<i class="fab fa-aws text-orange-400 text-sm"></i>';
                        } else if (lang === 'sql' || lang === 'mysql' || lang === 'postgresql') {
                            return '<i class="fas fa-database text-blue-700 text-sm"></i>';
                        } else if (lang === 'json') {
                            return '<i class="fas fa-code text-yellow-600 text-sm"></i>';
                        } else if (lang === 'markdown' || lang === 'md') {
                            return '<i class="fab fa-markdown text-gray-700 text-sm"></i>';
                        } else if (lang === 'xml') {
                            return '<i class="fas fa-file-code text-orange-500 text-sm"></i>';
                        } else if (lang === 'bash' || lang === 'shell' || lang === 'sh') {
                            return '<i class="fas fa-terminal text-green-600 text-sm"></i>';
                        } else if (lang === 'c') {
                            return '<i class="fas fa-code text-blue-600 text-sm"></i>';
                        } else if (lang === 'c++' || lang === 'cpp') {
                            return '<i class="fas fa-code text-blue-700 text-sm"></i>';
                        } else if (lang === 'c#' || lang === 'csharp') {
                            return '<i class="fas fa-code text-purple-600 text-sm"></i>';
                        } else if (lang === 'go' || lang === 'golang') {
                            return '<i class="fas fa-code text-cyan-600 text-sm"></i>';
                        } else if (lang === 'rust' || lang === 'powershell') {
                            return '<i class="fas fa-code text-orange-700 text-sm"></i>';
                        } else if (lang === 'swift') {
                            return '<i class="fab fa-swift text-orange-500 text-sm"></i>';
                        } else if (lang === 'kotlin') {
                            return '<i class="fas fa-code text-purple-500 text-sm"></i>';
                        } else if (lang === 'ruby') {
                            return '<i class="fas fa-gem text-red-600 text-sm"></i>';
                        } else if (lang === 'laravel') {
                            return '<i class="fab fa-laravel text-red-500 text-sm"></i>';
                        } else if (lang === 'sass' || lang === 'scss') {
                            return '<i class="fab fa-sass text-pink-500 text-sm"></i>';
                        } else if (lang === 'less') {
                            return '<i class="fab fa-less text-blue-600 text-sm"></i>';
                        } else if (lang === 'bootstrap') {
                            return '<i class="fab fa-bootstrap text-purple-600 text-sm"></i>';
                        } else if (lang === 'angular') {
                            return '<i class="fab fa-angular text-red-600 text-sm"></i>';
                        } else if (lang === 'npm') {
                            return '<i class="fab fa-npm text-red-500 text-sm"></i>';
                        } else if (lang === 'yarn') {
                            return '<i class="fab fa-yarn text-blue-500 text-sm"></i>';
                        } else if (lang === 'webpack') {
                            return '<i class="fab fa-webpack text-blue-400 text-sm"></i>';
                        } else {
                            // Default fallback
                            return `<div class="w-4 h-4 text-xs bg-gray-200 rounded flex items-center justify-center text-gray-600 font-medium">${language.substring(0, 2).toUpperCase()}</div>`;
                        }
                    },

                    search() {
                        // Clear previous timeout
                        if (this.searchTimeout) {
                            clearTimeout(this.searchTimeout);
                        }

                        // If query is too short, hide results
                        if (this.searchQuery.length < 2) {
                            this.showResults = false;
                            this.results = { snippets: [], folders: [] };
                            return;
                        }

                        // Show results and loading state
                        this.showResults = true;
                        this.loading = true;

                        // Debounce the search
                        this.searchTimeout = setTimeout(() => {
                            fetch(`/search?q=${encodeURIComponent(this.searchQuery)}`, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.results = data;
                                this.loading = false;
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                                this.loading = false;
                                this.results = { snippets: [], folders: [] };
                            });
                        }, 300); // 300ms debounce
                    },

                    hideResults() {
                        this.showResults = false;
                    }
                }
            }
        </script>

        <script>
            // Sidebar resizing functionality
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const resizeHandle = document.getElementById('sidebar-resize-handle');
                let isResizing = false;
                let startX = 0;
                let startWidth = 0;

                // Load saved width from localStorage
                const savedWidth = localStorage.getItem('sidebarWidth');
                if (savedWidth) {
                    sidebar.style.width = savedWidth + 'px';
                }

                resizeHandle.addEventListener('mousedown', function(e) {
                    // Only allow resizing on desktop
                    if (window.innerWidth < 768) return;

                    isResizing = true;
                    startX = e.clientX;
                    startWidth = sidebar.offsetWidth;
                    document.body.style.cursor = 'ew-resize';
                    document.body.style.userSelect = 'none';
                    e.preventDefault();
                });

                document.addEventListener('mousemove', function(e) {
                    if (!isResizing) return;

                    const deltaX = e.clientX - startX;
                    const newWidth = startWidth + deltaX;
                    const minWidth = parseInt(getComputedStyle(sidebar).minWidth);
                    const maxWidth = parseInt(getComputedStyle(sidebar).maxWidth);

                    if (newWidth >= minWidth && newWidth <= maxWidth) {
                        sidebar.style.width = newWidth + 'px';
                    }
                });

                document.addEventListener('mouseup', function() {
                    if (isResizing) {
                        isResizing = false;
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                        // Save width to localStorage
                        localStorage.setItem('sidebarWidth', sidebar.offsetWidth);
                    }
                });
            });
        </script>
    </body>
</html>
