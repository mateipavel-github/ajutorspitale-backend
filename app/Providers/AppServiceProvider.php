<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Observers\PostingChangeObserver; 
use App\PostingChange;
use DB;

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

        DB::listen(function ($query) {
            if(isset($_SESSION['log_sql'])) {

                $_SESSION['log_sql']['queries'][] = [
                    'query' => $query->sql,
                    'bindings' => $query->bindings
                ];
                $_SESSION['log_sql']['time'] += $query->time;

                $log = "QUERY LOG: \n" .$query->sql;
                if(count($query->bindings)>0) {
                    $log .= "\nBINDINGS: ".implode(',', $query->bindings);
                }
                $log .= "\nTIME: ". $query->time ."\n\n-----------------------------------------------\n\n";
                \Log::info($log);
            }
        });
    }
}
