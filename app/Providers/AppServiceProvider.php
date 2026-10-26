<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || env('VERCEL') || isset($_SERVER['VERCEL']) || request()->header('x-forwarded-proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(function () {
            return \Illuminate\Support\Facades\Auth::user()?->isCompany()
                ? route('company.dashboard')
                : route('dashboard');
        });
    }
}
