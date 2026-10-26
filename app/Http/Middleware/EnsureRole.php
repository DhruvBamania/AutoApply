<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Enforces role-based access control between 'user' and 'company'.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requiredRole ('user' or 'company')
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== $requiredRole) {
            // A company account tried to access candidate/user pages
            if ($user->role === 'company') {
                return redirect()->route('company.dashboard')
                    ->with('error', 'Access restricted. Candidate features are not accessible to company accounts.');
            }

            // A candidate/user tried to access company pages
            return redirect()->route('dashboard')
                ->with('error', 'Access restricted. Company features are only accessible to verified company accounts.');
        }

        return $next($request);
    }
}
