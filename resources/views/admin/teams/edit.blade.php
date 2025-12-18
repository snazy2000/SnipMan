@extends('layouts.snippets')

@section('title', 'Edit Team')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('admin.teams') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-friends mr-3 text-indigo-600 dark:text-indigo-400"></i>Edit Team
                </h1>
            </div>
            <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                Update team information and ownership
            </p>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <form action="{{ route('admin.teams.update', $team) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Team Name
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $team->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Owner -->
                <div class="mb-6">
                    <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Team Owner
                    </label>
                    <select id="owner_id" 
                            name="owner_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200"
                            required>
                        @foreach($allUsers as $user)
                            <option value="{{ $user->id }}" {{ old('owner_id', $team->owner_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('owner_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        The owner has full control over the team and its content.
                    </p>
                </div>

                <!-- Team Stats -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Team Statistics</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Members</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $team->members->count() }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Created</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $team->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Team Members -->
                @if($team->members->isNotEmpty())
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Members</h3>
                        <div class="space-y-2">
                            @foreach($team->members as $member)
                                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                        {{ ucfirst($member->pivot->role) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.teams') }}" 
                       class="px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded font-medium transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded font-medium transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
