<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SoftDeletes2 extends Migration
{
 
    public $tables = [
        'users',
        'help_requests',
        'help_request_changes',
        'help_request_change_needs',
        'metadata_change_types',
        'metadata_user_role_types',
        'metadata_counties',
        'metadata_medical_unit_types',
        'metadata_request_status_types',
        'metadata_need_types'
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        foreach($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }  
    }
}
