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

        $rolesTable = (new MetadataUserRoleType())->getTable();
    
        if (Schema::hasTable($rolesTable) && Schema::hasColumn($rolesTable, 'slug')) {
            $role_type_scopes = DB::table($rolesTable)->select('slug','label')->get();      
            
            if (!empty($role_type_scopes)) {
                $tokensCan = [];
                foreach($role_type_scopes as $role) {
                    $tokensCan[$role->slug] = $role->label;
                }
                Passport::tokensCan($tokensCan);
            }
        }

        Passport::routes();
    }
}
