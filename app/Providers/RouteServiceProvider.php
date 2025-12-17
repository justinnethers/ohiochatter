<?php

namespace App\Providers;

use App\Models\Region;
use App\Models\County;
use App\Models\City;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        parent::boot();

        // Custom route model binding for geographic hierarchy validation
        Route::bind('region', function ($value, $route) {
            return Region::where('slug', $value)->firstOrFail();
        });

        Route::bind('county', function ($value, $route) {
            $county = County::where('slug', $value)->firstOrFail();
            
            // Validate that county belongs to the region if region is present
            if ($route->hasParameter('region')) {
                $region = $route->parameter('region');
                if ($region instanceof Region && $county->region_id !== $region->id) {
                    abort(404, 'County not found in this region');
                }
            }
            
            return $county;
        });

        Route::bind('city', function ($value, $route) {
            $city = City::where('slug', $value)->firstOrFail();
            
            // Validate that city belongs to the county if county is present
            if ($route->hasParameter('county')) {
                $county = $route->parameter('county');
                if ($county instanceof County && $city->county_id !== $county->id) {
                    abort(404, 'City not found in this county');
                }
            }
            
            return $city;
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
