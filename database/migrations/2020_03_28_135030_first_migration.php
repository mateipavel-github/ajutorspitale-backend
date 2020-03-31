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

    protected $metadata = ['need_types','counties','medical_unit_types','change_types'];

    public function up()
    {
        Schema::create('help_requests', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('assigned_user_id')->nullable();
            $table->integer('medical_unit_id')->nullable();
            $table->integer('medical_unit_type_id')->nullable();
            $table->string('medical_unit_name')->nullable();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('job_title')->nullable();
            $table->integer('county_id')->nullable();
            $table->text('extra_info')->nullable();

            $table->longText('current_needs')->nullable()->comment('JSON representation of aggregate needs - sum of changes');
        });

        Schema::create('help_request_changes', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();

            $table->integer('change_type_id');

            $table->integer('help_request_id');
            $table->longText('changes')->comment('JSON with this change\'s new values');

            $table->integer('user_id')->comment('User who made the change');
            $table->text('user_comment')->comment('Reason for change');
            $table->set('status', ['new','approved','rejected','in_progress','final'])->default('new');

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

        Schema::create('help_request_change_needs', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
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
