<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            <i class="fas fa-code mr-2 text-indigo-600 dark:text-indigo-400"></i>Default Language
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Set your preferred default language for creating new snippets.
        </p>
    </header>

    <form method="post" action="{{ route('profile.language.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="default_monaco_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Default Language
            </label>
            <select id="default_monaco_language"
                    name="monaco_language"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="javascript" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                <option value="typescript" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'typescript' ? 'selected' : '' }}>TypeScript</option>
                <option value="python" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'python' ? 'selected' : '' }}>Python</option>
                <option value="php" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'php' ? 'selected' : '' }}>PHP</option>
                <option value="java" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'java' ? 'selected' : '' }}>Java</option>
                <option value="csharp" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'csharp' ? 'selected' : '' }}>C#</option>
                <option value="cpp" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'cpp' ? 'selected' : '' }}>C++</option>
                <option value="c" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'c' ? 'selected' : '' }}>C</option>
                <option value="go" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'go' ? 'selected' : '' }}>Go</option>
                <option value="rust" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'rust' ? 'selected' : '' }}>Rust</option>
                <option value="ruby" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'ruby' ? 'selected' : '' }}>Ruby</option>
                <option value="swift" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'swift' ? 'selected' : '' }}>Swift</option>
                <option value="kotlin" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'kotlin' ? 'selected' : '' }}>Kotlin</option>
                <option value="html" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'html' ? 'selected' : '' }}>HTML</option>
                <option value="css" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'css' ? 'selected' : '' }}>CSS</option>
                <option value="sql" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'sql' ? 'selected' : '' }}>SQL</option>
                <option value="bash" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'bash' ? 'selected' : '' }}>Bash</option>
                <option value="powershell" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'powershell' ? 'selected' : '' }}>PowerShell</option>
                <option value="json" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'json' ? 'selected' : '' }}>JSON</option>
                <option value="yaml" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'yaml' ? 'selected' : '' }}>YAML</option>
                <option value="xml" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'xml' ? 'selected' : '' }}>XML</option>
                <option value="markdown" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'markdown' ? 'selected' : '' }}>Markdown</option>
            </select>
            @error('monaco_language')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This will be pre-selected when you create new snippets</p>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save Language
            </button>

            @if (session('status') === 'language-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >Saved.</p>
            @endif
        </div>
    </form>
</section>
