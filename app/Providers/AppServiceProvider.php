<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Common\MyPaginator;
use App\Facades\GlobalSettingClass;
use \Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LengthAwarePaginator::class, MyPaginator::class);
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        $this->app->bind('GlobalSetting', function () {
            return new GlobalSettingClass();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->share('APP_NAME', env('APP_NAME'));
    }
}
