{{-- Recursive folder options for select dropdowns --}}
@foreach($folders as $folder)
    <option value="{{ $folder->id }}"
            {{ (isset($selectedId) && $selectedId == $folder->id) ? 'selected' : '' }}
            class="{{ $folderClass ?? '' }}"
            {!! isset($teamId) ? 'data-team-id="' . $teamId . '"' : '' !!}
            {!! isset($hideStyle) ? 'style="' . $hideStyle . '"' : '' !!}>
        {!! str_repeat('&nbsp;&nbsp;', $level) !!}@if($level > 0)└─ @endif{{ $folder->name }}
    </option>

    @if($folder->children->count() > 0)
        @include('partials.folder-options', [
            'folders' => $folder->children,
            'level' => $level + 1,
            'selectedId' => $selectedId ?? null,
            'folderClass' => $folderClass ?? '',
            'teamId' => $teamId ?? null,
            'hideStyle' => $hideStyle ?? null
        ])
    @endif
@endforeach
