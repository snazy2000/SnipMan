@extends('layouts.snippets')

@section('title', 'All Snippets')

@section('content')
<div x-data="bulkActions()" @keydown.escape.window="clearSelection()">

{{-- Header --}}
<div class="flex flex-wrap justify-between items-center gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">All Snippets</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 transition-colors duration-200">{{ ($personalSnippets->total() ?? 0) + $teamSnippets->count() }} total</p>
    </div>
    <div class="flex items-center gap-2">
        {{-- Export dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                <i class="fas fa-download text-xs"></i> Export
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                 class="absolute right-0 mt-1 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-10">
                <a href="{{ route('snippets.export') }}?format=json" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-lg">
                    <i class="fas fa-file-code text-xs text-indigo-500"></i> Export as JSON
                </a>
                <a href="{{ route('snippets.export') }}?format=zip" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-lg">
                    <i class="fas fa-file-archive text-xs text-amber-500"></i> Export as ZIP
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Bulk action bar --}}
<div x-show="selected.length > 0" x-cloak
     class="flex items-center gap-3 mb-4 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg">
    <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300" x-text="selected.length + ' selected'"></span>
    <button @click="bulkDelete()" class="px-3 py-1.5 text-xs font-medium bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors">
        <i class="fas fa-trash mr-1"></i> Delete
    </button>
    <button @click="clearSelection()" class="ml-auto text-xs text-indigo-500 dark:text-indigo-400 hover:underline">Clear</button>
</div>

@if(($personalSnippets && $personalSnippets->count() > 0) || $teamSnippets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($personalSnippets->merge($teamSnippets) as $snippet)
            <div class="relative bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md dark:hover:shadow-gray-900/25 transition-all duration-200"
                 :class="selected.includes({{ $snippet->id }}) ? 'ring-2 ring-indigo-500' : ''">

                {{-- Checkbox (shown on hover or when any selected) --}}
                <div class="absolute top-3 left-3 z-10"
                     :class="selected.length > 0 ? '' : 'opacity-0 group-hover:opacity-100 transition-opacity'"
                     x-show="selected.length > 0 || $el.closest('.relative:hover')"
                     @click.stop>
                    <input type="checkbox" :checked="selected.includes({{ $snippet->id }})"
                           @change="toggleSelect({{ $snippet->id }})"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                </div>

                <div class="cursor-pointer" onclick="window.location.href='{{ route('snippets.show', $snippet) }}'">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            @if($snippet->is_pinned)
                                <i class="fas fa-thumbtack text-amber-500 text-xs flex-shrink-0"></i>
                            @endif
                            <h3 class="font-medium text-gray-900 dark:text-gray-100 truncate transition-colors duration-200 text-sm">{{ $snippet->title }}</h3>
                        </div>
                        <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                            @if($snippet->hasAIAnalysis())
                                <span class="p-1 rounded-full text-xs bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300" title="AI Analyzed">
                                    <i class="fas fa-robot text-xs"></i>
                                </span>
                            @elseif($snippet->isAIProcessing() && $aiAutoDescriptionEnabled)
                                <span class="p-1 rounded-full text-xs bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300" title="AI Processing">
                                    <i class="fas fa-spinner fa-spin text-xs"></i>
                                </span>
                            @endif
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ ucfirst($snippet->language) }}
                            </span>
                        </div>
                    </div>

                    {{-- Description preview (manual or AI) --}}
                    @php $desc = $snippet->description ?: $snippet->ai_description; @endphp
                    @if($desc)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ $desc }}</p>
                    @endif

                    {{-- Code preview --}}
                    <div class="mb-2">
                        <pre class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-2 rounded overflow-hidden transition-colors duration-200" style="max-height: 80px;"><code>{{ Str::limit($snippet->content, 150) }}</code></pre>
                    </div>

                    {{-- Tags --}}
                    @if($snippet->user_tags && count($snippet->user_tags))
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach(array_slice($snippet->user_tags, 0, 3) as $tag)
                                <span class="px-1.5 py-0.5 text-xs rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">{{ $tag }}</span>
                            @endforeach
                            @if(count($snippet->user_tags) > 3)
                                <span class="text-xs text-gray-400">+{{ count($snippet->user_tags) - 3 }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        <span>{{ $snippet->updated_at->diffForHumans() }}</span>
                        @if($snippet->folder)
                            <span class="text-gray-400 dark:text-gray-500">{{ $snippet->folder->name }}</span>
                        @elseif($snippet->owner_type === 'App\Models\Team')
                            <span class="text-gray-400 dark:text-gray-500">{{ $snippet->owner->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($personalSnippets && $personalSnippets->hasPages())
        <div class="mt-6">
            {{ $personalSnippets->links() }}
        </div>
    @endif
@else
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg transition-colors duration-200">
            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2 transition-colors duration-200">No snippets yet</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6 transition-colors duration-200">Create your first code snippet to get started.</p>
        <a href="{{ route('snippets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create your first snippet
        </a>
    </div>
@endif

</div>

<script>
function bulkActions() {
    return {
        selected: [],

        toggleSelect(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
        },

        clearSelection() {
            this.selected = [];
        },

        async bulkDelete() {
            if (!this.selected.length) return;
            if (!confirm(`Delete ${this.selected.length} snippet(s)? This cannot be undone.`)) return;

            const resp = await fetch('{{ route('snippets.bulk') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ action: 'delete', ids: this.selected }),
            });

            if (resp.ok) {
                window.location.reload();
            }
        },
    };
}
</script>
@endsection
