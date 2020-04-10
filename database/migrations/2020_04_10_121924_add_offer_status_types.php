<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferStatusTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('metadata_offer_status_types');
        Schema::dropIfExists('metadata_delivery_status_types');

        Schema::create('metadata_offer_status_types', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('label');
            $table->string('slug')->nullable();

        });

        DB::table('metadata_offer_status_types')->insert([
            ['slug' =>'approved', 'label'=>'Aprobată'],
            ['slug' =>'rejected', 'label'=>'Respinsă'],
            ['slug'=>'complete', 'label'=>'Consumată'],
            ['slug'=>'withdrawn', 'label'=>'Revocată']
        ]);

        $defaultRequestStatusId = DB::table('metadata_offer_status_types')->insertGetId([
            'slug'=>'new', 
            'label'=>'Nouă'
        ]);

        Schema::table('help_offers', function (Blueprint $table) use($defaultRequestStatusId) {
            $table->integer('status')->default($defaultRequestStatusId)->change();
        });

        Schema::create('metadata_delivery_status_types', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('label');
            $table->string('slug')->nullable();

        });

        DB::table('metadata_delivery_status_types')->insert([
            ['slug' =>'waiting-for-courier', 'label'=>'Chemat curier'],
            ['slug' =>'in-delivery', 'label'=>'Preluată de curier'],
            ['slug'=>'delivered', 'label'=>'Livrată'],
            ['slug'=>'cancelled', 'label'=>'Anulată']
        ]);

        $defaultDeliveryStatusId = DB::table('metadata_delivery_status_types')->insertGetId([
            'slug'=>'new', 
            'label'=>'În pregătire'
        ]);

        Schema::table('deliveries', function (Blueprint $table) use($defaultDeliveryStatusId) {
            $table->integer('status')->unsigned()->default($defaultDeliveryStatusId)->charset('')->collation('')->change();
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
        Schema::dropIfExists('metadata_offer_status_types');
        Schema::dropIfExists('metadata_delivery_status_types');

    }
}
