<?php

namespace App\Providers;

use App\Tools\LoadBalancer;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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

        Gate::define('manage',function ($user) {
            return $user->is_admin > 0;
        });

        // filament hooks
        Filament::registerRenderHook('scripts.end', function (){

            $scripts = [];

            // add jquery is edit page of article
            if(Route::getCurrentRoute()->getName() == 'filament.resources.articles.edit') {
                $scripts[] = sprintf('<script defer src="%s"></script>', asset('admin/jquery.min.js'));
                $scripts[] = sprintf('<script defer src="%s"></script>', asset('admin/actions.js'));
            }

            return implode("\n", $scripts);
        });

        Filament::registerRenderHook('styles.end', function (){

            return sprintf("<link rel=\"stylesheet\" href=\"%s\" />", asset('admin/manual.css'));
        });


    }
}
