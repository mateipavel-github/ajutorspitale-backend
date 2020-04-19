<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeliveryPlanningRequestNeedUrgency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('metadata_delivery_plan_status_types', function(Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('label');
            $table->string('slug')->nullable();
        });

        DB::table('metadata_delivery_plan_status_types')->insert([
            ['slug' =>'approved', 'label'=>'Aprobat'],
            ['slug'=>'complete', 'label'=>'Finalizat']
        ]);

        $defaultDeliveryPlanStatusId = DB::table('metadata_delivery_plan_status_types')->insertGetId([
            'slug'=>'draft', 
            'label'=>'În lucru'
        ]);

        Schema::create('delivery_plans', function(Blueprint $table) use ($defaultDeliveryPlanStatusId) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title')->nullable()->comment('User-friendly identifier i.e. "Livrare viziere de la X de Paște"');
            $table->integer('user_id')->unsigned()->comment('User who created the delivery plan');
            $table->integer('assigned_user_id')->unsigned()->nullable()->comment('User who is responsible for the delivery plan');
            $table->text('details')->nullable()->comment('JSON representation of how many items are available for this delivery');
            $table->integer('status')->unsigned()->default($defaultDeliveryPlanStatusId);
        });

        Schema::create('delivery_plan_posting', function(Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->string('item_type');
            $table->integer('item_id')->unsigned();
            $table->integer('delivery_plan_id')->unsigned();

            $table->integer('position')->unsigned()->default(0);
            $table->integer('priority_group')->unsigned()->default(1);

            $table->integer('delivery_id')->unsigned()->nullable();
            $table->text('details')->nullable()->comment('JSON representation of how many items of each type were included in this plan');
        });

        Schema::table('posting_change_needs', function (Blueprint $table) {
            $table->integer('urgency')->nullable()->default(0);
        });

        Schema::dropIfExists('delivery_help_offer');
        Schema::dropIfExists('delivery_help_request');

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
