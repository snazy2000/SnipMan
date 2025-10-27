@extends('layouts.snippets')

@section('title', 'Create Folder')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">Create New Folder</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors duration-200">Organize your snippets with a new folder</p>
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

        <form action="{{ route('folders.store') }}" method="POST">
            @csrf

            <!-- Folder Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                    Folder Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('name') border-red-500 @enderror"
                       placeholder="Enter folder name"
                       required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Owner Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                    Owner <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <!-- Personal Folder -->
                    <label class="flex items-center">
                        <input type="radio"
                               name="owner_type"
                               value="personal"
                               class="text-indigo-600 focus:ring-indigo-500"
                               {{ old('owner_type', 'personal') == 'personal' ? 'checked' : '' }}
                               onchange="updateOwnerOptions()">
                        <span class="ml-2 text-sm text-gray-700">Personal Folder</span>
                    </label>

                    <!-- Team Folder -->
                    @if($teams->count() > 0)
                        <label class="flex items-center">
                            <input type="radio"
                                   name="owner_type"
                                   value="team"
                                   class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('owner_type') == 'team' ? 'checked' : '' }}
                                   onchange="updateOwnerOptions()">
                            <span class="ml-2 text-sm text-gray-700">Team Folder</span>
                        </label>
                    @endif
                </div>
                @error('owner_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Team Selection (only shown when Team Folder is selected) -->
            @if($teams->count() > 0)
                <div id="team-selection" class="mb-6" style="{{ old('owner_type') == 'team' ? '' : 'display: none;' }}">
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Team <span class="text-red-500">*</span>
                    </label>
                    <select id="team_id"
                            name="team_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('team_id') border-red-500 @enderror">
                        <option value="">Choose a team...</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                                @if($team->pivot->role === 'owner')
                                    (Owner)
                                @elseif($team->pivot->role === 'editor')
                                    (Editor)
                                @else
                                    (Member)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Parent Folder (optional) -->
            @if($folders->count() > 0)
                <div class="mb-6">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Parent Folder (Optional)
                    </label>
                    <select id="parent_id"
                            name="parent_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">No parent (top level)</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ old('parent_id') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                                @if($folder->owner_type === 'App\Models\Team')
                                    (Team: {{ $folder->owner->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Hidden inputs for owner_id -->
            <input type="hidden" name="user_id" value="{{ Auth::id() }}">

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('folders.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateOwnerOptions() {
    const ownerTypeRadios = document.getElementsByName('owner_type');
    const teamSelection = document.getElementById('team-selection');

    for (let radio of ownerTypeRadios) {
        if (radio.checked) {
            if (radio.value === 'team') {
                teamSelection.style.display = 'block';
            } else {
                teamSelection.style.display = 'none';
            }
            break;
        }
    }
}
</script>
@endsection
