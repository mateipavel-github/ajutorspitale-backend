<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\HelpRequestChange;
use App\Observers\HelpRequestChangeObserver;

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

        Schema::defaultStringLength(191);

        if(env('DEBUG_SQL', false)) {
            \DB::listen(function ($query) {
                \Log::info(
                    '[SQL]: '.$query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }

        HelpRequestChange::observe(HelpRequestChangeObserver::class);

    }
}
