<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangedMedicalUnitNameInHelpRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_requests', function (Blueprint $table) {
            $table->string('medical_unit_name', 512)->nullable()->change();
            $table->string('job_title', 512)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('help_requests', function (Blueprint $table) {
            $table->string('medical_unit_name', 512)->nullable(false)->change();
            $table->string('job_title', 512)->nullable(false)->change();
        });
    }
}
