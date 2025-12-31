<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class InvitationController extends Controller
{
    /**
     * Show the invitation acceptance form.
     */
    public function show($token)
    {
        $hashedToken = hash('sha256', $token);

        $user = User::where('invitation_token', $hashedToken)
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid or has already been used.');
        }

        return view('auth.accept-invitation', compact('user', 'token'));
    }

    /**
     * Process the invitation acceptance.
     */
    public function accept(Request $request, $token)
    {
        $hashedToken = hash('sha256', $token);

        $user = User::where('invitation_token', $hashedToken)
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid or has already been used.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Update user details
        $user->name = $validated['name'];
        $user->password = Hash::make($validated['password']);
        $user->invitation_token = null;
        $user->invitation_accepted_at = now();
        $user->email_verified_at = now(); // Mark email as verified
        $user->save();

        // Accept any pending team invitations for this user
        \DB::table('team_user')
            ->where('user_id', $user->id)
            ->where('invitation_status', 'pending')
            ->update([
                'invitation_status' => 'accepted',
                'invitation_token' => null,
                'updated_at' => now(),
            ]);

        // Log the user in
        Auth::login($user);

        return redirect()->route('snippets.index')->with('success', 'Welcome to '.config('app.name').'! Your account has been successfully set up.');
    }
}
