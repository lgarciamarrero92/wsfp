<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Validator;
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
        Validator::extend('tooBigForSolarFarm', function($attribute, $value, $parameters) {
            if($parameters[0] == 'solar' && $value > 140000)return false;
            return true;
        });
        Schema::defaultStringLength(191);
    }
}
