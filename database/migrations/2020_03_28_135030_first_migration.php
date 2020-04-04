<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FirstMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $metadata = ['need_types','counties','medical_unit_types','change_types','user_role_types','request_status_types'];

    public function up()
    {

        //create metadata
        foreach($this -> metadata as $type) {
            Schema::create('metadata_'.$type, function (Blueprint $table) {

                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->string('label');
                $table->string('slug')->nullable();

            });
        }

        //populate metadata

        //request status types
        DB::table('metadata_request_status_types')->insert([
            ['slug' =>'approved', 'label'=>'Aprobată'],
            ['slug' =>'rejected', 'label'=>'Respinsă'],
            ['slug'=>'complete', 'label'=>'Rezolvată']
        ]);
        $defaultRequestStatusId = DB::table('metadata_request_status_types')->insertGetId([
            'slug'=>'new', 
            'label'=>'Nouă'
        ]);

        //request change types
        DB::table('metadata_change_types')->insert([
            ['slug' =>'new_request', 'label'=>'Cerere nouă'],
            ['slug' =>'error', 'label'=>'Greșeală'],
            ['slug'=>'solicitor_update', 'label'=>'Modificare cerută de solicitant'],
            ['slug'=>'delivery', 'label'=>'Livrare']
        ]);

        //user roles
        DB::table('metadata_user_role_types')->insert([
            ['slug'=>'delivery_agent', 'label'=>'Agent livrare']
        ]);
        $volunteerRoleId = DB::table('metadata_user_role_types')->insertGetId([
            'slug'=>'volunteer', 
            'label'=>'Voluntar'
        ]);
        $adminRoleId = DB::table('metadata_user_role_types')->insertGetId([
            'slug'=>'admin', 
            'label'=>'Administrator'
        ]);

        Schema::table("users", function (Blueprint $table) use ($volunteerRoleId) {
            $table->integer('role_type_id')->unsigned()->default($volunteerRoleId);
            $table->string('phone_number')->unique()->nullable();
            $table->softDeletes();
        });

        //add one admin user
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@ajutorspitale.ro',
            'phone_number' => '1234567890',
            'password' => Hash::make('hartieigienica'),
            'role_type_id' => $adminRoleId
        ]);
        
        //add medical unit types
        $list = [
            ['label'=>'spital județean de stat', 'slug'=>'state-hospital-county'], 
            ['label'=>'spital orășenesc de stat', 'slug'=>'state-hospital-city'], 
            ['label'=>'alt fel de spital de stat (maternitate; cu o anumită specializare; etc)', 'slug'=>'state-hospital-other'], 
            ['label'=>'spital privat', 'slug'=>'private-hospital'], 
            ['label'=>'direcția ambulanțe', 'slug'=>'state-ambulance'], 
            ['label'=>'medic de familie', 'slug'=>'family-doctor']
        ];
        foreach($list as $item) {
            DB::table('metadata_medical_unit_types')->insert([
                'label' => $item['label'],
                'slug' => $item['slug']
            ]);
        }

        //add counties
        $list = ['Alba','Argeș','Arad','București','Bacău','Bihor','Bistrița Năsăud','Brăila','Botoșani','Brașov','Buzău','Cluj','Călărași','Caraș-Severin','Constanța','Covasna','Dâmbovița','Dolj','Gorj','Galați','Giurgiu','Hunedoara','Harghita','Ilfov','Ialomița','Iași','Mehedinți','Maramureș','Mureș','Neamț','Olt','Prahova','Sibiu','Sălaj','Satu-Mare','Suceava','Tulcea','Timiș','Teleorman','Vâlcea','Vrancea'];
        foreach($list as $item) {
            DB::table('metadata_counties')->insert([
                'label' => $item
            ]);
        }


        Schema::create('help_requests', function (Blueprint $table) use ($defaultRequestStatusId) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('user_id')->nullable();
            $table->integer('assigned_user_id')->nullable();
            $table->integer('medical_unit_id')->nullable();
            $table->integer('medical_unit_type_id')->nullable();
            $table->string('medical_unit_name')->nullable();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('job_title')->nullable();
            $table->integer('county_id')->nullable();
            $table->text('extra_info')->nullable();
            $table->text('needs_text')->nullable();
            $table->integer('status')->unsigned()->default($defaultRequestStatusId);

            $table->longText('current_needs')->nullable()->comment('JSON representation of aggregate needs - sum of changes');
        });

        Schema::create('help_request_changes', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->integer('change_type_id');

            $table->integer('help_request_id');
            $table->longText('change_log')->comment('JSON with this change\'s new values');

            $table->integer('user_id')->comment('User who made the change')->nullable();
            $table->text('user_comment')->comment('Reason for change')->nullable();
            $table->set('status', ['new','approved','rejected','in_progress','final'])->default('new');

        });

        Schema::create('help_request_change_needs', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('help_request_change_id');
            $table->integer('need_type_id');
            $table->string('quantity');
        });

    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('help_requests');
        Schema::dropIfExists('help_request_changes');
        Schema::dropIfExists('help_request_change_needs');
        Schema::dropIfExists('deliveries');

        foreach($this -> metadata as $type) {
            Schema::dropIfExists('metadata_'.$type);
        }

    }
}
