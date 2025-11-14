<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    // App\Providers\AppServiceProvider.php
    public function boot()
    {
      view()->composer('*', function ($view) {
        if(request()->route('project')){
            $view->with('project', request()->route('project'));
        }
    });

    
        if (env('APP_ENV') === 'test') {
        URL::forceScheme('https');
    }
    
}

}
