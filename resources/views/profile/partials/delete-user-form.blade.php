<section class="space-y-6">
    <header class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-200">
            <i class="fas fa-exclamation-triangle mr-2 text-red-600 dark:text-red-400"></i>{{ __('Delete Account') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-red-500 transition-colors duration-200">
        <i class="fas fa-trash-alt mr-2"></i>{{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600 dark:text-red-400"></i>{{ __('Are you sure you want to delete your account?') }}
                </h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Password') }}</label>
                <input id="password" name="password" type="password"
                       class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-transparent transition-colors duration-200"
                       placeholder="{{ __('Password') }}">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" x-on:click="$dispatch('close')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-gray-500 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>{{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-red-500 transition-colors duration-200">
                    <i class="fas fa-trash-alt mr-2"></i>{{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
