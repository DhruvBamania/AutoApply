<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceWeeklySessionExpiration
{
    /**
     * Handle an incoming request.
     * Enforces that sessions expire after 7 days (604,800 seconds).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // If the session does not have a recorded login timestamp, initialize it
            if (! $request->session()->has('login_time')) {
                $request->session()->put('login_time', now()->timestamp);
            }

            $loginTime = (int) $request->session()->get('login_time');
            $sevenDaysInSeconds = 7 * 24 * 60 * 60; // 7 days = 604,800 seconds

            if ((now()->timestamp - $loginTime) >= $sevenDaysInSeconds) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your 7-day session has expired. Please log in again.');
            }
        }

        return $next($request);
    }
}
