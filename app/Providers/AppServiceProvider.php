<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Massa;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
use App\Observers\MassaObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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
        // =====================================================
        // REGISTER MODEL OBSERVERS (Cache Invalidation)
        // =====================================================
        Event::observe(EventObserver::class);
        Massa::observe(MassaObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);

        // =====================================================
        // CONFIGURE RATE LIMITERS
        // =====================================================
        $this->configureRateLimiting();

        // =====================================================
        // PRODUCTION OPTIMIZATIONS
        // =====================================================
        if ($this->app->environment('production')) {
            // Prevent lazy loading in production (catch N+1)
            Model::preventLazyLoading();
            
            // Disable DB query debugging
            DB::disableQueryLog();
        }

        // =====================================================
        // DEVELOPMENT HELPERS
        // =====================================================
        if ($this->app->environment('local')) {
            // Enable query log for debugging
            DB::enableQueryLog();
        }
    }

    /**
     * Configure rate limiters for the application
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limit: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Stricter limit for public registration
        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Stricter limit for NIK lookup (prevent enumeration)
        RateLimiter::for('nik-lookup', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Higher limit for authenticated check-in operations
        RateLimiter::for('checkin', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(20)->by($request->ip());
        });

        // Very strict limit for login attempts
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(10)->by($request->input('email')),
            ];
        });
    }
}

