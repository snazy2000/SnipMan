<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_disabled || $user->trashed())) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account disabled.'], 403);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Your account has been disabled.']);
        }

        return $next($request);
    }
}
