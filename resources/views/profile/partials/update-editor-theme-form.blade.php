<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            <i class="fas fa-palette mr-2 text-indigo-600 dark:text-indigo-400"></i>Editor Theme
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Choose your preferred Monaco editor theme. Preview it below before saving.
        </p>
    </header>

    <form method="post" action="{{ route('profile.theme.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Theme Selection -->
            <div>
                <label for="monaco_theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Theme
                </label>
                <select id="monaco_theme"
                        name="monaco_theme"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onchange="changeEditorTheme(this.value)">
                    <optgroup label="Light Themes">
                        <option value="vs" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'vs' ? 'selected' : '' }}>Visual Studio Light</option>
                        <option value="active4d" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'active4d' ? 'selected' : '' }}>Active4D</option>
                        <option value="github" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'github' ? 'selected' : '' }}>GitHub</option>
                        <option value="chrome" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'chrome' ? 'selected' : '' }}>Chrome</option>
                        <option value="clouds" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'clouds' ? 'selected' : '' }}>Clouds</option>
                        <option value="textmate" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'textmate' ? 'selected' : '' }}>TextMate</option>
                    </optgroup>
                    <optgroup label="Dark Themes">
                        <option value="vs-dark" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'vs-dark' ? 'selected' : '' }}>Visual Studio Dark</option>
                        <option value="monokai" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'monokai' ? 'selected' : '' }}>Monokai</option>
                        <option value="dracula" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'dracula' ? 'selected' : '' }}>Dracula</option>
                        <option value="tomorrow-night" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'tomorrow-night' ? 'selected' : '' }}>Tomorrow Night</option>
                        <option value="tomorrow-night-blue" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'tomorrow-night-blue' ? 'selected' : '' }}>Tomorrow Night Blue</option>
                        <option value="tomorrow-night-bright" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'tomorrow-night-bright' ? 'selected' : '' }}>Tomorrow Night Bright</option>
                        <option value="tomorrow-night-eighties" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'tomorrow-night-eighties' ? 'selected' : '' }}>Tomorrow Night 80s</option>
                        <option value="solarized-dark" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'solarized-dark' ? 'selected' : '' }}>Solarized Dark</option>
                        <option value="solarized-light" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'solarized-light' ? 'selected' : '' }}>Solarized Light</option>
                        <option value="nord" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'nord' ? 'selected' : '' }}>Nord</option>
                        <option value="night-owl" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'night-owl' ? 'selected' : '' }}>Night Owl</option>
                        <option value="oceanic-next" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'oceanic-next' ? 'selected' : '' }}>Oceanic Next</option>
                        <option value="one-dark-pro" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'one-dark-pro' ? 'selected' : '' }}>One Dark Pro</option>
                        <option value="cobalt" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'cobalt' ? 'selected' : '' }}>Cobalt</option>
                        <option value="blackboard" {{ old('monaco_theme', $user->monaco_theme ?? 'vs-dark') == 'blackboard' ? 'selected' : '' }}>Blackboard</option>
                    </optgroup>
                </select>
                @error('monaco_theme')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Language Selection -->
            <div>
                <label for="monaco_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Preview Language
                </label>
                <select id="monaco_language"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onchange="changeEditorLanguage(this.value)">
                    <option value="javascript" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                    <option value="typescript" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'typescript' ? 'selected' : '' }}>TypeScript</option>
                    <option value="python" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'python' ? 'selected' : '' }}>Python</option>
                    <option value="php" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'php' ? 'selected' : '' }}>PHP</option>
                    <option value="java" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'java' ? 'selected' : '' }}>Java</option>
                    <option value="csharp" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'csharp' ? 'selected' : '' }}>C#</option>
                    <option value="cpp" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'cpp' ? 'selected' : '' }}>C++</option>
                    <option value="go" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'go' ? 'selected' : '' }}>Go</option>
                    <option value="rust" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'rust' ? 'selected' : '' }}>Rust</option>
                    <option value="ruby" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'ruby' ? 'selected' : '' }}>Ruby</option>
                    <option value="html" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'html' ? 'selected' : '' }}>HTML</option>
                    <option value="css" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'css' ? 'selected' : '' }}>CSS</option>
                    <option value="sql" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'sql' ? 'selected' : '' }}>SQL</option>
                    <option value="shell" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'shell' ? 'selected' : '' }}>Bash/Shell</option>
                    <option value="json" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'json' ? 'selected' : '' }}>JSON</option>
                    <option value="yaml" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'yaml' ? 'selected' : '' }}>YAML</option>
                    <option value="xml" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'xml' ? 'selected' : '' }}>XML</option>
                    <option value="markdown" {{ old('monaco_language', $user->monaco_language ?? 'javascript') == 'markdown' ? 'selected' : '' }}>Markdown</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Test how your theme looks with different languages (preview only)</p>
            </div>
        </div>

        <!-- Monaco Editor Preview -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Preview
            </label>
            <div id="editor-preview" class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden" style="height: 400px;"></div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save Theme
            </button>

            @if (session('status') === 'theme-updated')
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

@push('styles')
<style>
    #editor-preview {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<!-- Monaco Editor -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs/loader.js"></script>
<script>
@verbatim
    let previewEditor;

    // Sample code for different languages
    const sampleCodeByLanguage = {
        javascript: `// Sample JavaScript Code
function fibonacci(n) {
    if (n <= 1) return n;
    return fibonacci(n - 1) + fibonacci(n - 2);
}

// Example usage
const numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
const fibResults = numbers.map(n => ({
    number: n,
    fibonacci: fibonacci(n)
}));

console.log('Fibonacci Results:', fibResults);

// ES6+ Features
const asyncExample = async () => {
    try {
        const response = await fetch('https://api.example.com/data');
        const data = await response.json();
        return data.filter(item => item.active);
    } catch (error) {
        console.error('Error fetching data:', error);
        throw error;
    }
};

// Class example
class CodeSnippet {
    constructor(title, language, content) {
        this.title = title;
        this.language = language;
        this.content = content;
        this.createdAt = new Date();
    }

    display() {
        return \`\${this.title} (\${this.language})\`;
    }
}`,
        typescript: `// Sample TypeScript Code
interface User {
    id: number;
    name: string;
    email: string;
    isActive: boolean;
}

class UserService {
    private users: User[] = [];

    addUser(user: User): void {
        this.users.push(user);
    }

    getUserById(id: number): User | undefined {
        return this.users.find(user => user.id === id);
    }

    getActiveUsers(): User[] {
        return this.users.filter(user => user.isActive);
    }
}

// Generic function
function identity<T>(arg: T): T {
    return arg;
}

const stringResult = identity<string>("Hello TypeScript");
const numberResult = identity<number>(42);`,
        python: `# Sample Python Code
def fibonacci(n):
    """Calculate the nth Fibonacci number."""
    if n <= 1:
        return n
    return fibonacci(n - 1) + fibonacci(n - 2)

# List comprehension example
squares = [x**2 for x in range(10)]

# Class example
class CodeSnippet:
    def __init__(self, title, language, content):
        self.title = title
        self.language = language
        self.content = content

    def display(self):
        return f"{self.title} ({self.language})"

    @staticmethod
    def create_from_dict(data):
        return CodeSnippet(
            data['title'],
            data['language'],
            data['content']
        )

# Async/await example
import asyncio

async def fetch_data():
    await asyncio.sleep(1)
    return {"status": "success", "data": [1, 2, 3]}`,
        php: '<' + '?php\\n' +
`// Sample PHP Code
namespace App\\\\Services;

class SnippetService
{
    private array \\$snippets = [];

    public function addSnippet(array \\$data): void
    {
        \\$this->snippets[] = [
            'title' => \\$data['title'],
            'language' => \\$data['language'],
            'content' => \\$data['content'],
            'created_at' => now(),
        ];
    }

    public function getSnippetsByLanguage(string \\$language): array
    {
        return array_filter(\\$this->snippets, function(\\$snippet) use (\\$language) {
            return \\$snippet['language'] === \\$language;
        });
    }

    public function searchSnippets(string \\$query): array
    {
        return array_filter(\\$this->snippets, function(\\$snippet) use (\\$query) {
            return str_contains(\\$snippet['title'], \\$query) ||
                   str_contains(\\$snippet['content'], \\$query);
        });
    }
}

// Using the service
\\$service = new SnippetService();
\\$service->addSnippet([
    'title' => 'Hello World',
    'language' => 'php',
    'content' => 'echo "Hello, World!";'
]);`,
        java: `// Sample Java Code
import java.util.*;
import java.util.stream.*;

public class CodeSnippet {
    private String title;
    private String language;
    private String content;
    private Date createdAt;

    public CodeSnippet(String title, String language, String content) {
        this.title = title;
        this.language = language;
        this.content = content;
        this.createdAt = new Date();
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public static void main(String[] args) {
        List<CodeSnippet> snippets = Arrays.asList(
            new CodeSnippet("Hello", "java", "System.out.println();"),
            new CodeSnippet("Loop", "java", "for (int i = 0; i < 10; i++) {}")
        );

        snippets.stream()
            .filter(s -> s.getLanguage().equals("java"))
            .forEach(s -> System.out.println(s.getTitle()));
    }

    public String getLanguage() {
        return language;
    }
}`,
        html: `<!-- Sample HTML Code -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Snippet Manager</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <h1>SnippetMan</h1>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#snippets">Snippets</a></li>
                <li><a href="#profile">Profile</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <section class="snippet-list">
            <h2>Your Snippets</h2>
            <div class="snippet-card">
                <h3>Hello World</h3>
                <p class="language">JavaScript</p>
                <pre><code>console.log('Hello, World!');</code></pre>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 SnippetMan</p>
    </footer>
</body>
</html>`,
        css: `/* Sample CSS Code */
:root {
    --primary-color: #4f46e5;
    --secondary-color: #10b981;
    --background: #ffffff;
    --text-color: #1f2937;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, sans-serif;
    background-color: var(--background);
    color: var(--text-color);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.snippet-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.snippet-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
}`,
        json: `{
  "name": "snippetman",
  "version": "1.0.0",
  "description": "A Laravel application for managing code snippets",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "test": "pest"
  },
  "dependencies": {
    "@tailwindcss/forms": "^0.5.7",
    "alpinejs": "^3.13.3",
    "monaco-editor": "^0.54.0"
  },
  "devDependencies": {
    "autoprefixer": "^10.4.16",
    "laravel-vite-plugin": "^1.0.0",
    "postcss": "^8.4.32",
    "tailwindcss": "^3.4.0",
    "vite": "^5.0.0"
  },
  "keywords": [
    "laravel",
    "snippets",
    "code",
    "monaco"
  ],
  "author": "SnippetMan",
  "license": "MIT"
}`,
        sql: `-- Sample SQL Code
-- Create snippets table
CREATE TABLE snippets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    language VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    folder_id UUID REFERENCES folders(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_language (language),
    INDEX idx_user_id (user_id)
);

-- Insert sample data
INSERT INTO snippets (title, language, content, user_id)
VALUES
    ('Hello World', 'javascript', 'console.log("Hello");', '123e4567-e89b-12d3-a456-426614174000'),
    ('Fibonacci', 'python', 'def fib(n): return n if n <= 1 else fib(n-1) + fib(n-2)', '123e4567-e89b-12d3-a456-426614174000');

-- Query snippets by language
SELECT s.title, s.language, u.name as author, s.created_at
FROM snippets s
INNER JOIN users u ON s.user_id = u.id
WHERE s.language = 'javascript'
ORDER BY s.created_at DESC
LIMIT 10;

-- Update snippet
UPDATE snippets
SET content = 'console.log("Updated!");', updated_at = CURRENT_TIMESTAMP
WHERE id = '123e4567-e89b-12d3-a456-426614174001';`,
        yaml: `# Sample YAML Configuration
name: SnippetMan CI/CD
version: '1.0'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_PORT=5432
    volumes:
      - ./storage:/app/storage
    depends_on:
      - db
      - redis

  db:
    image: postgres:16
    environment:
      POSTGRES_DB: snippetman
      POSTGRES_USER: dbuser
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

volumes:
  postgres_data:
    driver: local

networks:
  app_network:
    driver: bridge`,
        default: `// Select a language to see sample code
// This editor supports syntax highlighting
// for many programming languages!

function example() {
    return "Choose a language from the dropdown above";
}`
    };

    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.54.0/min/vs' } });

    // Theme definitions from monaco-themes
    const themeData = {
        'monokai': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Monokai.json',
        'dracula': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Dracula.json',
        'github': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/GitHub.json',
        'tomorrow-night': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Tomorrow-Night.json',
        'tomorrow-night-blue': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Tomorrow-Night-Blue.json',
        'tomorrow-night-bright': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Tomorrow-Night-Bright.json',
        'tomorrow-night-eighties': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Tomorrow-Night-Eighties.json',
        'solarized-dark': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Solarized-dark.json',
        'solarized-light': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Solarized-light.json',
        'nord': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Nord.json',
        'night-owl': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Night-Owl.json',
        'oceanic-next': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Oceanic-Next.json',
        'one-dark-pro': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/OneDark-Pro.json',
        'cobalt': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Cobalt.json',
        'blackboard': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Blackboard.json',
        'active4d': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Active4D.json',
        'chrome': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Chrome-DevTools.json',
        'clouds': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/Clouds.json',
        'textmate': 'https://cdn.jsdelivr.net/npm/monaco-themes@0.4.4/themes/textmate-mac-classic.json'
    };

    require(['vs/editor/editor.main'], function() {
        const currentTheme = document.getElementById('monaco_theme').value;
        const currentLanguage = document.getElementById('monaco_language').value;
        const sampleCode = sampleCodeByLanguage[currentLanguage] || sampleCodeByLanguage.default;

        // Create editor with initial theme and language
        previewEditor = monaco.editor.create(document.getElementById('editor-preview'), {
            value: sampleCode,
            language: currentLanguage,
            theme: currentTheme === 'vs' || currentTheme === 'vs-dark' ? currentTheme : 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: true },
            fontSize: 14,
            lineNumbers: 'on',
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            cursorStyle: 'line',
            wordWrap: 'on'
        });

        // Load custom theme if needed
        if (themeData[currentTheme]) {
            loadAndDefineTheme(currentTheme);
        }
    });

    async function loadAndDefineTheme(themeName) {
        if (themeName === 'vs' || themeName === 'vs-dark') {
            monaco.editor.setTheme(themeName);
            return;
        }

        const themeUrl = themeData[themeName];
        if (!themeUrl) {
            console.warn('Theme not found:', themeName);
            return;
        }

        try {
            const response = await fetch(themeUrl);
            let themeJson = await response.json();

            // Add base property if missing
            if (!themeJson.base) {
                const isLightTheme = ['github', 'chrome', 'clouds', 'textmate', 'solarized-light', 'active4d'].includes(themeName);
                themeJson.base = isLightTheme ? 'vs' : 'vs-dark';
            }

            monaco.editor.defineTheme(themeName, themeJson);
            monaco.editor.setTheme(themeName);
        } catch (error) {
            console.error('Error loading theme:', error);
            monaco.editor.setTheme('vs-dark');
        }
    }

    function changeEditorTheme(themeName) {
        if (previewEditor) {
            loadAndDefineTheme(themeName);
        }
    }

    function changeEditorLanguage(language) {
        if (previewEditor) {
            const sampleCode = sampleCodeByLanguage[language] || sampleCodeByLanguage.default;
            const model = previewEditor.getModel();
            monaco.editor.setModelLanguage(model, language);
            previewEditor.setValue(sampleCode);
        }
    }
@endverbatim
</script>
@endpush
