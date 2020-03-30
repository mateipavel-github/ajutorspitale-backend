<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddedRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('metadata_user_role_types', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->increments("id");
            $table->string('label', 64)->nullable();
        });
        DB::table('metadata_user_role_types')->insert([
            ["label" => "Admin"],
            ["label" => "Volunteer"],
            ["label" => "Delivery agent"],
            ["label" => "Requisitor"]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("metadata_user_role_types");
    }
}
