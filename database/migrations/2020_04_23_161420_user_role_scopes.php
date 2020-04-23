<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserRoleScopes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metadata_user_role_types', function (Blueprint $table) {
            if(!Schema::hasColumn('metadata_user_role_types', 'scopes')) {
                $table->text('scopes')->nullable();
            }
        });

        $role_scopes = [
            'admin' => 'requests.list,requests.edit,offers.list,offers.edit,exports,deliveryplans.list,deliveryplans.edit,users.list,users.edit,metadata.edit,deliveries.list,deliveries.edit',
            'volunteer' => 'requests.list,requests.edit,offers.list,offers.edit,deliveryplans.list',
            'solutions_volunteer' => 'requests.edit,offers.edit,exports,deliveryplans.list,deliveryplans.edit,deliveries.list,deliveries.edit'
        ];
        
        foreach($role_scopes as $slug=>$scopes) {
            DB::update('update metadata_user_role_types set scopes = ? where slug = ?', [$scopes, $slug]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
