@extends('layouts.snippets')

@section('title', 'Profile Settings')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-circle mr-3 text-indigo-600 dark:text-indigo-400"></i>Profile Settings
                </h1>
            </div>
            <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                Manage your account settings and preferences
            </p>
        </div>

        <div class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-colors duration-200 p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-colors duration-200 p-6">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete Account -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-colors duration-200 p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
