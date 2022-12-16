<?php

namespace App\Providers;

use App\Tools\LoadBalancer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->singleton('balancer', function ($app) {
            return new LoadBalancer();
        });
    }
}
