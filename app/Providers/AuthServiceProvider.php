<?php

namespace App\Providers;

use App\MetadataUserRoleType;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Schema;


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

        if (Schema::hasTable((new MetadataUserRoleType())->getTable())) {
            $role_type_scopes = MetadataUserRoleType::all()->pluck('label', 'label')->toArray();
            if (!empty($role_type_scopes)) {
                Passport::tokensCan($role_type_scopes);
            }
        }

        Passport::routes();
    }
}
