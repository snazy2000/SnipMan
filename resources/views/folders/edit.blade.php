@extends('layouts.snippets')

@section('title', 'Edit Folder')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-200">Edit Folder</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors duration-200">Update folder details</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200">
        <form action="{{ route('folders.update', $folder) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Folder Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                    Folder Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $folder->name) }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200 @error('name') border-red-500 @enderror"
                       placeholder="Enter folder name"
                       required>
                @error('name')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Owner Information (read-only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">Owner</label>
                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    @if($folder->owner_type === 'App\Models\Team')
                        Team: {{ $folder->owner->name }}
                    @else
                        Personal Folder
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">Owner cannot be changed after creation</p>
            </div>

            <!-- Parent Folder -->
            @if($folders->count() > 0)
                <div class="mb-6">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200">
                        Parent Folder (Optional)
                    </label>
                    <select id="parent_id"
                            name="parent_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors duration-200">
                        <option value="">No parent (top level)</option>
                        @foreach($folders as $availableFolder)
                            @if($availableFolder->id !== $folder->id) <!-- Prevent self-parenting -->
                                <option value="{{ $availableFolder->id }}"
                                        {{ old('parent_id', $folder->parent_id) == $availableFolder->id ? 'selected' : '' }}>
                                    {{ $availableFolder->name }}
                                    @if($availableFolder->owner_type === 'App\Models\Team')
                                        (Team: {{ $availableFolder->owner->name }})
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">
                        Choose a parent folder to organize this folder under another folder
                    </p>
                </div>
            @endif

            <!-- Folder Stats -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg transition-colors duration-200">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3 transition-colors duration-200">Folder Contents</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 transition-colors duration-200">Snippets:</span>
                        <span class="font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->snippets->count() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 transition-colors duration-200">Subfolders:</span>
                        <span class="font-medium text-gray-900 dark:text-white transition-colors duration-200">{{ $folder->children->count() }}</span>
                    </div>
                </div>
                @if($folder->children->count() > 0)
                    <div class="mt-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200">
                            <strong>Note:</strong> Moving this folder will also move all subfolders and snippets within it.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                <div class="flex space-x-3">
                    <a href="{{ route('folders.show', $folder) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors duration-200">
                        Cancel
                    </a>
                    @can('delete', $folder)
                        <button type="button"
                                onclick="confirmDelete()"
                                class="px-4 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 rounded-lg transition-colors duration-200">
                            Delete Folder
                        </button>
                    @endcan
                </div>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    Update Folder
                </button>
            </div>
        </form>

        <!-- Delete Form (hidden) -->
        @can('delete', $folder)
            <form id="delete-form" action="{{ route('folders.destroy', $folder) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this folder? This action cannot be undone. All snippets and subfolders will also be deleted.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
