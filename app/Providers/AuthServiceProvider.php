<?php

namespace App\Providers;

use App\MetadataUserRoleType;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addDays(15));

        $rolesTable = (new MetadataUserRoleType())->getTable();
    
        if (Schema::hasTable($rolesTable) && Schema::hasColumn($rolesTable, 'slug')) {
            $scopes = array_unique(explode(',', implode(',', DB::table($rolesTable)->select('scopes')->pluck('scopes')->all())));      
            
            if (!empty($scopes)) {
                $tokensCan = [];
                foreach($scopes as $scope) {
                    $tokensCan[$scope] = $scope;
                }
                Passport::tokensCan($tokensCan);
            }
        }

        Passport::routes();
    }
}
