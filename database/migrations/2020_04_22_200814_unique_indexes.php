<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UniqueIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('delivery_plan_posting', function(Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('delivery_plan_posting');
            if(!array_key_exists("request_in_delivery_plan_unique", $indexesFound)) {
                $table->unique(['delivery_plan_id','item_type','item_id'], 'request_in_delivery_plan_unique');
            }
        });
            
        Schema::table('delivery_needs', function(Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('delivery_needs');
            if(!array_key_exists("need_in_delivery_unique", $indexesFound)) {
                $table->unique(['delivery_id','need_type_id'], 'need_in_delivery_unique');
            }
        });

        Schema::table('deliveries', function(Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->text('destination_address')->nullable()->change();
            $table->integer('destination_county_id')->unsigned()->nullable()->change();
        });

        Schema::table('posting_changes', function(Blueprint $table) {
            if(!Schema::hasColumn('posting_changes', 'delivery_id')) {
                $table->integer('delivery_id')->unsigned()->nullable();
            }
        });
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
