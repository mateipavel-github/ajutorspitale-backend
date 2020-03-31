<?php

namespace App\Providers;

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

        if(env('DEBUG_SQL', false)) {
            \DB::listen(function ($query) {
                \Log::info(
                    '[SQL]: '.$query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }

    }
}
