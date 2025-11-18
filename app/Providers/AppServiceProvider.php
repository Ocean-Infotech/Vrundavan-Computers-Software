<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// use Laravel\Passport\Passport;

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
        /** This is use for Spaite Direct Permission check */
        Blade::if('directCan', function ($permission) {
            $directPermissionArray = Auth::user()->permissions->pluck('name');
            return Auth::check() && count($directPermissionArray) > 0 && in_array($permission, $directPermissionArray->toArray());
        });

        /** This is use for Spaite Direct Any Permission check */
        Blade::if('directCanAny', function ($permissions) {
            return Auth::check() && Auth::user()->permissions->whereIn('name', (array) $permissions)->isNotEmpty();
        });

        // Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
        // Passport::hashClientSecrets();

        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        View::share('firebaseConfig', config('constants.firebase'));
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
