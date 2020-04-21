<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeliveryPlanSponsors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('delivery_plans', function (Blueprint $table) {
            if(!Schema::hasColumn('delivery_plans', 'main_sponsor_id')) {
                $table->integer('main_sponsor_id')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'delivery_sponsor_id')) {
                $table->integer('delivery_sponsor_id')->nullable();
            }
        });

        Schema::table('deliveries', function (Blueprint $table) {
            if(!Schema::hasColumn('deliveries', 'packages')) {
                $table->integer('packages')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'size')) {
                $table->integer('size')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'weight')) {
                $table->integer('weight')->nullable();
            }
            
            if(!Schema::hasColumn('deliveries', 'destination_contact_name')) {
                $table->renameColumn('contact_name', 'destination_contact_name');
            }
            if(!Schema::hasColumn('deliveries', 'destination_phone_number')) {
                $table->renameColumn('contact_phone_number', 'destination_phone_number');
            }
            if(!Schema::hasColumn('deliveries', 'destination_phone_number')) {
                $table->renameColumn('county_id', 'destination_county_id');
            }
            

            if(!Schema::hasColumn('deliveries', 'destination_name')) {
                $table->string('destination_name')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'destination_city_name')) {
                $table->string('destination_city_name')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_name')) {
                $table->string('sender_name')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_contact_name')) {
                $table->string('sender_contact_name')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_phone_number')) {
                $table->string('sender_phone_number')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_city_name')) {
                $table->string('sender_city_name')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_address')) {
                $table->text('sender_address')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'sender_county_id')) {
                $table->integer('sender_county_id')->nullable();
            }
        });

        Schema::table('delivery_plans', function (Blueprint $table) {

            if(!Schema::hasColumn('delivery_plans', 'sender_name')) {
                $table->string('sender_name')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'sender_contact_name')) {
                $table->string('sender_contact_name')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'sender_phone_number')) {
                $table->string('sender_phone_number')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'sender_address')) {
                $table->text('sender_address')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'sender_city_name')) {
                $table->string('sender_city_name')->nullable();
            }
            if(!Schema::hasColumn('delivery_plans', 'sender_county_id')) {
                $table->integer('sender_county_id')->nullable();
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
