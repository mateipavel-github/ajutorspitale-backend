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

        if (Schema::hasTable($rolesTable = (new MetadataUserRoleType())->getTable())) {
            $role_type_scopes = DB::table($rolesTable)->select('label')->get()->pluck('slug');
            if (!empty($role_type_scopes['items'])) {
                Passport::tokensCan($role_type_scopes['items']);
            }
        }

        Passport::routes();
    }
}
