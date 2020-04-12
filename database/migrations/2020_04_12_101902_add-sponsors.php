<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSponsors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (!Schema::hasTable('sponsors')) {
            
            Schema::create('sponsors', function (Blueprint $table) {

                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->string('name');

            });
        }

        Schema::table('deliveries', function (Blueprint $table) {
            if(!Schema::hasColumn('deliveries', 'main_sponsor_id')) {
                $table->integer('main_sponsor_id')->nullable();
            }
            if(!Schema::hasColumn('deliveries', 'delivery_sponsor_id')) {
                $table->integer('delivery_sponsor_id')->nullable();
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
        
    }
}
