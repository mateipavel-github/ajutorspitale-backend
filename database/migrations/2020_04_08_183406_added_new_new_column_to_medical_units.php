<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedNewNewColumnToMedicalUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medical_units', function (Blueprint $table) {
            $table->string('name_without_diacritics')->nullable()->after("name");
            $table->string('name_without_council')->nullable()->after("name_without_diacritics");
            $table->string('latitude', 32)->nullable()->after("city_id");
            $table->string('longitude', 32)->nullable()->after('latitude');
            $table->string('facebook_page', 1024)->nullable()->after('longitude');
            $table->string('website', 512)->nullable()->after('facebook_page');
            $table->timestamps();
        });

        DB::table('metadata_medical_unit_types')->insert([
            'label' => "specialitate",
            'slug' => "specialitate"
        ]);

        DB::table('metadata_medical_unit_types')->insert([
            'label' => "altceva",
            'slug' => "altceva"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medical_units', function (Blueprint $table) {
            $table->dropColumn(["latitude", 'longitude', 'name_without_diacritics', 'name_without_council', 'facebook_page', 'website', "created_at", 'updated_at']);
        });
        DB::table('metadata_medical_unit_types')->where(['slug'=>"specialitate"])->delete();
        DB::table('metadata_medical_unit_types')->where(['slug'=>"altceva"])->delete();
    }
}
