<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Common\MyPaginator;
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
