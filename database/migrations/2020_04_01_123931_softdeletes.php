<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Softdeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //empty table
        DB::table('metadata_change_types')->truncate();

        //add slug column
        Schema::table('metadata_change_types', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        //insert new data
        $list = [
            ['slug' =>'new_request', 'label'=>'Cerere nouă'],
            ['slug' =>'error', 'label'=>'Greșeală'],
            ['slug'=>'solicitor_update', 'label'=>'Modificare cerută de solicitant'],
            ['slug'=>'delivery', 'label'=>'Livrare']
        ];

        foreach($list as $item) {
            DB::table('metadata_change_types')->insert([
                'slug'  => $item['slug'],
                'label' => $item['label'],
                'status' => 'active'
            ]);
        }

        //empty table
        DB::table('metadata_user_role_types')->truncate();

        //add slug & status column
        Schema::table('metadata_user_role_types', function (Blueprint $table) {
            $table->timestamps();
            $table->string('slug')->nullable();
            $table->set('status', ['active','inactive'])->default('active');
        });

        //insert new data
        $list = [
            ['slug' =>'admin', 'label'=>'Administrator'],
            ['slug' =>'volunteer', 'label'=>'Voluntar'],
            ['slug'=>'delivery_agent', 'label'=>'Agent livrare']
        ];

        foreach($list as $item) {
            DB::table('metadata_user_role_types')->insert([
                'slug'  => $item['slug'],
                'label' => $item['label'],
                'status' => 'active'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metadata_user_role_types', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('status');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        Schema::table('metadata_change_types', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
