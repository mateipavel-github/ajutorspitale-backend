<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Observers\PostingChangeObserver; 
use App\PostingChange;

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
        $this->app->singleton('metadata', function ($app) {
            return new \App\Helpers\MetadataHelper;
        });
        
        $this->app->singleton('texthelper', function ($app) {
            return new \App\Helpers\TextHelper;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultStringLength(191);

        PostingChange::observe(PostingChangeObserver::class);

    }
}
