<section x-data="apiTokens()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">API Tokens</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Generate tokens to access your snippets from the SnippetMan Chrome extension or other API clients.
        </p>
    </header>

    <div class="mt-4 space-y-4">
        <!-- Create token form -->
        <div class="flex gap-3">
            <input type="text" x-model="newTokenName" placeholder="Token name (e.g. Chrome Extension)"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            <button @click="createToken" :disabled="!newTokenName.trim() || creating"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-md transition-colors">
                <span x-show="!creating">Generate Token</span>
                <span x-show="creating">Generating…</span>
            </button>
        </div>

        <!-- Newly created token reveal -->
        <div x-show="plainTextToken" x-cloak
            class="rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 p-4">
            <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">
                Copy your token now — it won't be shown again.
            </p>
            <div class="flex gap-2 items-center">
                <code x-text="plainTextToken"
                    class="flex-1 text-xs bg-white dark:bg-gray-800 border border-green-300 dark:border-green-600 rounded px-3 py-2 font-mono break-all text-gray-800 dark:text-gray-200"></code>
                <button @click="copyToken"
                    class="shrink-0 px-3 py-2 text-xs bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors">
                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                </button>
            </div>
        </div>

        <!-- Existing tokens list -->
        <div x-show="tokens.length > 0">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Active Tokens</h3>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                <template x-for="token in tokens" :key="token.id">
                    <li class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800">
                        <div>
                            <span x-text="token.name" class="text-sm font-medium text-gray-900 dark:text-gray-100"></span>
                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400"
                                x-text="'Created ' + new Date(token.created_at).toLocaleDateString()"></span>
                            <template x-if="token.last_used_at">
                                <span class="ml-2 text-xs text-gray-400 dark:text-gray-500"
                                    x-text="'· Last used ' + new Date(token.last_used_at).toLocaleDateString()"></span>
                            </template>
                        </div>
                        <button @click="revokeToken(token.id)"
                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                            Revoke
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <p x-show="tokens.length === 0 && !plainTextToken"
            class="text-sm text-gray-500 dark:text-gray-400 italic">No active tokens.</p>
    </div>
</section>

<script>
function apiTokens() {
    return {
        tokens: [],
        newTokenName: '',
        creating: false,
        plainTextToken: '',
        copied: false,

        async init() {
            await this.loadTokens();
        },

        async loadTokens() {
            const resp = await fetch('/profile/tokens');
            if (resp.ok) this.tokens = await resp.json();
        },

        async createToken() {
            if (!this.newTokenName.trim()) return;
            this.creating = true;
            const resp = await fetch('/profile/tokens', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ name: this.newTokenName.trim() }),
            });
            if (resp.ok) {
                const data = await resp.json();
                this.plainTextToken = data.token;
                this.newTokenName = '';
                await this.loadTokens();
            }
            this.creating = false;
        },

        async revokeToken(id) {
            if (!confirm('Revoke this token? Any client using it will lose access.')) return;
            const resp = await fetch(`/profile/tokens/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (resp.ok) await this.loadTokens();
        },

        async copyToken() {
            await navigator.clipboard.writeText(this.plainTextToken);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
    };
}
</script>
