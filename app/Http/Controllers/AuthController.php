<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 1. Invalidate other devices via Laravel's built-in logoutOtherDevices
            try {
                Auth::logoutOtherDevices($credentials['password']);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('logoutOtherDevices warning: ' . $e->getMessage());
            }

            // 2. Delete all other active database sessions for this user so other devices get logged out immediately
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', Auth::id())
                ->where('id', '!=', $request->session()->getId())
                ->delete();

            // 3. Track login timestamp for 7-day session duration
            $request->session()->put('login_time', now()->timestamp);

            if (Auth::user()->isCompany()) {
                return redirect()->route('company.dashboard')->with('success', 'Welcome back to your Company Portal!');
            }

            return redirect()->intended('dashboard')->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'account_type' => 'required|in:user,company',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($request->input('account_type') === 'company') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['website'] = 'nullable|string|max:255';
            $rules['location'] = 'nullable|string|max:255';
            $rules['description'] = 'nullable|string|max:2000';
        }

        $validated = $request->validate($rules);

        $role = $validated['account_type'];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        if ($role === 'company') {
            \App\Models\CompanyProfile::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'website' => $validated['website'] ?? null,
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Delete any other sessions for this user
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        $request->session()->put('login_time', now()->timestamp);

        if ($role === 'company') {
            return redirect()->route('company.dashboard')->with('success', 'Company account registered successfully! Welcome to your Employer Portal.');
        }

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
