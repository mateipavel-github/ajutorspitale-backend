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

            if(!Schema::hasColumn('medical_units', 'name_without_diacritics')) {
                $table->string('name_without_diacritics')->nullable()->after("name");
            }

            if(!Schema::hasColumn('medical_units', 'name_without_council')) {
                $table->string('name_without_council')->nullable()->after("name_without_diacritics");
            }

            if(!Schema::hasColumn('medical_units', 'latitude')) {
                $table->string('latitude', 32)->nullable()->after("city_id");
            }
            
            if(!Schema::hasColumn('medical_units', 'longitude')) {
                $table->string('longitude', 32)->nullable()->after('latitude');
            }

            if(!Schema::hasColumn('medical_units', 'facebook_page')) {
                $table->string('facebook_page', 1024)->nullable()->after('longitude');
            }

            if(!Schema::hasColumn('medical_units', 'website')) {
                $table->string('website', 512)->nullable()->after('facebook_page');
            }

            if(!Schema::hasColumn('medical_units', 'created_at')) {
                $table->timestamps();
            }
            
        });

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

        // $columnsToDrop = ["latitude", 'longitude', 'name_without_diacritics', 'name_without_council', 'facebook_page', 'website', "created_at", 'updated_at'];
        // Schema::table('medical_units', function (Blueprint $table) use ($columnsToDrop) {
        //     foreach($columnsToDrop as $c) {
        //         if(Schema::hasColumn('medical_units', $c)) {
        //             $table->dropColumn($c);
        //         }
        //     }
        // });
        
        DB::table('metadata_medical_unit_types')->where(['slug'=>"altceva"])->delete();
    }
}
