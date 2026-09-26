<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's editor theme preference.
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monaco_theme' => ['required', Rule::in([
                'vs', 'vs-dark', 'active4d', 'github', 'chrome', 'clouds', 'textmate',
                'monokai', 'dracula', 'tomorrow-night', 'tomorrow-night-blue',
                'tomorrow-night-bright', 'tomorrow-night-eighties',
                'solarized-dark', 'solarized-light',
            ])],
        ]);

        $request->user()->update([
            'monaco_theme' => $validated['monaco_theme'],
        ]);

        return Redirect::route('profile.edit')->with('status', 'theme-updated');
    }

    /**
     * Update the user's default language preference.
     */
    public function updateLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monaco_language' => ['required', Rule::in([
                'javascript', 'typescript', 'python', 'php', 'java', 'csharp', 'cpp', 'c',
                'go', 'rust', 'ruby', 'swift', 'kotlin', 'html', 'css', 'sql', 'bash',
                'powershell', 'json', 'yaml', 'xml', 'markdown', 'plaintext',
            ])],
        ]);

        $request->user()->update([
            'monaco_language' => $validated['monaco_language'],
        ]);

        return Redirect::route('profile.edit')->with('status', 'language-updated');
    }

    /**
     * List the user's API tokens.
     */
    public function tokens(Request $request)
    {
        return response()->json(
            $request->user()->tokens()->select('id', 'name', 'created_at', 'last_used_at')->get()
        );
    }

    /**
     * Create a new API token.
     */
    public function createToken(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $token = $request->user()->createToken($request->name);

        return response()->json(['token' => $token->plainTextToken]);
    }

    /**
     * Revoke an API token.
     */
    public function revokeToken(Request $request, int $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return response()->json(['message' => 'Token revoked']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Close any public share links before the account goes away; snippet
        // ownership is polymorphic so nothing cascades on delete.
        $user->revokePublicShares();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
