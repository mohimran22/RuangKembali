<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use App\Helpers\ActiveRole;
use TakiElias\Tablar\Tablar;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
                URL::forceScheme('https');
            }
            view()->composer('*', function ($view) {
            $tablar = app(Tablar::class);
            $view->with('tablar', $tablar);
        });

        Blade::if('activerole', function ($roleName) {
            return strtolower(ActiveRole::name()) === strtolower($roleName);
        });

        Blade::if('activeperm', function ($permission) {
            return ActiveRole::hasPermission($permission);
        });
        Carbon::setLocale('id');
    }
}