<!-- Monaco Theme Configuration -->
<script>
    // Theme definitions from monaco-themes
    const monacoThemeData = {
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

    // User's preferred theme
    const userMonacoTheme = '{{ auth()->user()->monaco_theme ?? "vs-dark" }}';
    const userMonacoLanguage = '{{ auth()->user()->monaco_language ?? "javascript" }}';

    // Load and define custom theme if needed (but don't set it yet)
    async function loadMonacoTheme(themeName) {
        // Wait for Monaco to be available
        if (typeof monaco === 'undefined') {
            console.error('Monaco is not loaded yet');
            return 'vs-dark';
        }

        console.log('Loading theme:', themeName);

        // Built-in themes don't need loading
        if (themeName === 'vs' || themeName === 'vs-dark') {
            return themeName;
        }

        const themeUrl = monacoThemeData[themeName];
        if (!themeUrl) {
            console.warn('Theme not found:', themeName, '- falling back to vs-dark');
            return 'vs-dark';
        }

        try {
            const response = await fetch(themeUrl);
            const themeJson = await response.json();

            console.log('Theme JSON loaded:', themeName);

            // Ensure the theme has a base property (required for proper text colors)
            if (!themeJson.base) {
                // Determine if it's a light or dark theme based on common indicators
                const isLightTheme = ['github', 'chrome', 'clouds', 'textmate', 'solarized-light', 'active4d'].includes(themeName);
                themeJson.base = isLightTheme ? 'vs' : 'vs-dark';
                console.log('Set base theme to:', themeJson.base);
            }

            // Define the theme for Monaco
            monaco.editor.defineTheme(themeName, themeJson);

            console.log('Theme defined successfully:', themeName);

            return themeName;
        } catch (error) {
            console.error('Error loading theme:', themeName, error);
            return 'vs-dark';
        }
    }
</script>
