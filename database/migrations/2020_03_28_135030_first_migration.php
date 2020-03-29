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

    protected $metadata = ['needs','counties','medical_unit_types'];

    public function up()
    {
        Schema::create('help_requests', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('medical_unit_id')->nullable();
            $table->string('medical_unit_name')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_phone_number')->nullable();
            $table->string('user_job_title')->nullable();
            $table->integer('county_id')->nullable();
            $table->text('extra_info')->nullable();
        });

        foreach($this -> metadata as $type) {
            Schema::create('metadata_'.$type, function (Blueprint $table) {

                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->id();
                $table->timestamps();
                $table->string('label')->nullable();
                $table->set('status', ['active', 'inactive']);

            });
        }

        Schema::create('help_request_needs', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->integer('help_request_id');
            $table->integer('need_id');
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

        foreach($this -> metadata as $type) {
            Schema::dropIfExists('metadata_'.$type);
        }
        Schema::dropIfExists('help_request_needs');
    }
}
