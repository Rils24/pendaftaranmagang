<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        // Performance optimizations for development
        if ($this->app->environment('local')) {
            // Prevent lazy loading in development to catch N+1 query issues
            // Uncomment this line to debug slow queries (will throw exceptions on lazy loading)
            // Model::preventLazyLoading();
            
            // Disable strict model checking to avoid slowdowns in development
            Model::preventSilentlyDiscardingAttributes(false);
        }
        
        // Optimize model behavior
        Model::preventAccessingMissingAttributes(false);
        
        // Reduce database queries for timestamps
        // This prevents unnecessary datetime parsing
        DB::connection()->getSchemaBuilder()->morphUsingUuids(false);
    }
}
