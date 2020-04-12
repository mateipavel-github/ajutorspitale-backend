<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeliveryNeeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('delivery_needs')) {
            
            Schema::create('delivery_needs', function (Blueprint $table) {

                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->integer('delivery_id')->unsigned();
                $table->integer('need_type_id')->unsigned();
                $table->integer('quantity')->unsigned();
                $table->integer('production_sponsor_id')->unsigned()->nullable()->comment('Not implemented yet. In case one delivery sends products from multiple sponsors');
                $table->integer('financial_sponsor_id')->unsigned()->nullable()->comment('Not implemented yet. In case there was a financial donation to support production');

            }); 
        }

        if(!Schema::hasTable('delivery_help_request')) {
            Schema::rename('delivery_help_requests', 'delivery_help_request');
        }
        if(!Schema::hasTable('delivery_help_offer')) {
            Schema::rename('delivery_help_offers', 'delivery_help_offer');
        }

        
        Schema::table('deliveries', function(Blueprint $table) {
            if(!Schema::hasColumn('deliveries', 'county_id')) {
                $table->integer('county_id')->unsigned();
            }
            if(!Schema::hasColumn('deliveries', 'user_id')) {
                $table->integer('user_id')->unsigned()->nullable();
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
