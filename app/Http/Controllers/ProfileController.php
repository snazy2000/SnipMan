<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
            'monaco_theme' => ['required', 'string', 'max:50'],
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
            'monaco_language' => ['required', 'string', 'max:50'],
        ]);

        $request->user()->update([
            'monaco_language' => $validated['monaco_language'],
        ]);

        return Redirect::route('profile.edit')->with('status', 'language-updated');
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

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
