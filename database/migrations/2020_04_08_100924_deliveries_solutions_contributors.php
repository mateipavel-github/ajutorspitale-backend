<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeliveriesSolutionsContributors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        // to create: 
        // help_offers (id, name, phone_number, job_title, organization_name, county_ids, medical_unit_name, other_needs, comments) 
        // help_offer_needs (id, help_offer_id, need_type_id, quantity)
        // deliveries: (id, description, destination_medical_unit_id, destination_address, contact_name, contact_phone_number, status [pending,onroute,delivered])
        // delivery_help_requests (id, help_request_id, delivery_id)
        // delivery_help_offers (id, help_offer_id, delivery_id)
        // delivery_needs (delivery_id, need_type_id, quantity)

        Schema::dropIfExists('help_offers');
        Schema::dropIfExists('help_offer_counties');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('delivery_help_requests');
        Schema::dropIfExists('delivery_help_offers');

        Schema::create('help_offers', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->string('phone_number');
            $table->string('job_title')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('medical_unit_id')->nullable();
            $table->string('medical_unit_name')->nullable();
            $table->integer('assigned_user_id')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->text('other_needs')->nullable();
            $table->text('current_needs')->nullable();
            $table->text('extra_info')->nullable();
            $table->text('needs_text')->nullable();
            $table->integer('status')->unsigned();            

        });

        Schema::create('help_offer_counties', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('county_id')->unsigned();
            $table->integer('help_offer_id')->unsigned();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->text('description');
            $table->integer('destination_medical_unit_id')->nullable();
            $table->text('destination_address');
            $table->string('contact_name');
            $table->string('contact_phone_number');
            $table->set('status', ['pending','onroute','delivered','canceled'])->default('pending');
        });

        Schema::create('delivery_help_requests', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('help_request_id')->unsigned();
            $table->integer('delivery_id')->unsigned();
        });

        Schema::create('delivery_help_offers', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('help_offer_id')->unsigned();
            $table->integer('delivery_id')->unsigned();
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
        Schema::dropIfExists('help_offers');
        Schema::dropIfExists('help_offer_counties');

        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('delivery_help_requests');
        Schema::dropIfExists('delivery_help_offers');
    }

}
