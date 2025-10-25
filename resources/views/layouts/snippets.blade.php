<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Snippet Manager') - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen">
            <!-- Top Navigation Bar -->
            <nav class="bg-white border-b border-gray-200 fixed w-full top-0 z-50">
                <div class="px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                                <h1 class="text-xl font-semibold text-gray-900">Snippet Manager</h1>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6">
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" placeholder="Search snippets, folders" class="w-96 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- NEW SNIPPET Button -->
                            <a href="{{ route('snippets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                NEW SNIPPET
                            </a>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="ml-2 text-gray-700">{{ Auth::user()->name }}</span>
                                    <svg class="ml-1 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                    <div class="py-1">
                                        <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                            <div class="font-medium">{{ Auth::user()->name }}</div>
                                            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                        </div>
                                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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

            <div class="flex h-screen pt-16"> <!-- pt-16 to account for top navigation -->
                <!-- Sidebar -->
                <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
                    <!-- Folders Section -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">FOLDERS</h3>
                            <button class="text-gray-400 hover:text-gray-600" title="Add Folder">
                                <a href="{{ route('folders.create') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </a>
                            </button>
                        </div>

                        <!-- Personal Folders -->
                        @php
                            $personalFolders = Auth::user()->folders()->whereNull('parent_id')->with('children')->get();
                        @endphp

                        @if($personalFolders->isEmpty())
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500 mb-2">No folders yet</p>
                                <a href="{{ route('folders.create') }}" class="text-xs text-blue-600 hover:text-blue-800">Create your first folder</a>
                            </div>
                        @else
                            @foreach($personalFolders as $folder)
                                <div class="mb-2">
                                    <a href="{{ route('folders.show', $folder) }}" class="flex items-center px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        {{ $folder->name }}
                                    </a>
                                </div>
                            @endforeach
                        @endif

                        <!-- Team Folders -->
                        @php
                            $teamFolders = collect();
                            foreach(Auth::user()->teams as $team) {
                                $folders = $team->folders()->whereNull('parent_id')->with('children')->get();
                                foreach($folders as $folder) {
                                    $folder->team_name = $team->name;
                                }
                                $teamFolders = $teamFolders->merge($folders);
                            }
                        @endphp

                        @if($teamFolders->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">TEAM FOLDERS</h3>
                                @foreach($teamFolders as $folder)
                                    <div class="mb-2">
                                        <a href="{{ route('folders.show', $folder) }}" class="flex items-center px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                            <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <div class="truncate">{{ $folder->name }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ $folder->team_name }}</div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- No Folder Section -->
                        <div class="mt-6">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">NO FOLDER</h3>

                            @php
                                $unfoldered = Auth::user()->snippets()->whereNull('folder_id')->latest()->take(5)->get();
                            @endphp

                            @forelse($unfoldered as $snippet)
                                <div class="mb-2">
                                    <a href="{{ route('snippets.show', $snippet) }}" class="flex items-center px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <div class="w-4 h-4 mr-2 text-xs bg-gray-200 rounded flex items-center justify-center">
                                            {{ strtoupper(substr($snippet->language, 0, 2)) }}
                                        </div>
                                        <span class="truncate">{{ $snippet->title }}</span>
                                    </a>
                                </div>
                            @empty
                                <!-- Empty state handled above -->
                            @endforelse
                        </div>
                    </div>

                    <!-- Sidebar Footer -->
                    <div class="p-4 border-t border-gray-200">
                        <div class="flex space-x-2">
                            <a href="{{ route('teams.index') }}" class="flex-1 text-center py-2 px-3 text-xs text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded {{ request()->routeIs('teams.*') ? 'bg-gray-100 text-gray-800' : '' }}">
                                Teams
                            </a>
                            <a href="{{ route('folders.index') }}" class="flex-1 text-center py-2 px-3 text-xs text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded {{ request()->routeIs('folders.*') ? 'bg-gray-100 text-gray-800' : '' }}">
                                Folders
                            </a>
                            <a href="{{ route('snippets.index') }}" class="flex-1 text-center py-2 px-3 text-xs text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded {{ request()->routeIs('snippets.*') ? 'bg-gray-100 text-gray-800' : '' }}">
                                Snippets
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col overflow-hidden bg-gray-50">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="bg-green-50 border-l-4 border-green-400 p-4" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-white">
                        <div class="h-full p-6">
                            @yield('content')
                        </div>
                    </main>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
