@extends('layouts.snippets')

@section('title', 'Edit User')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('admin.users') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-edit mr-3 text-indigo-600 dark:text-indigo-400"></i>Edit User
                </h1>
            </div>
            <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                Update user information and permissions
            </p>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Name
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Super Admin -->
                @if($user->id !== auth()->id())
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="is_super_admin"
                                   name="is_super_admin"
                                   value="1"
                                   {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 border-gray-300 dark:border-gray-600 rounded">
                            <label for="is_super_admin" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 transition-colors duration-200">
                                <i class="fas fa-shield-alt mr-1 text-purple-600 dark:text-purple-400"></i>Super Administrator
                            </label>
                        </div>
                        <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                            Super admins have full access to all system features including user and team management.
                        </p>
                    </div>
                @endif

                <!-- Password Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Change Password</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Leave blank to keep the current password.</p>

                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                            New Password
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                            Confirm New Password
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.users') }}"
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
