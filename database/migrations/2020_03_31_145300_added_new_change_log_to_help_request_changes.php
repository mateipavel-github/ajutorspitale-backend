<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedNewChangeLogToHelpRequestChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_request_changes', function (Blueprint $table) {
            $table->renameColumn('changes', 'change_log');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('help_request_changes', function (Blueprint $table) {
            $table->renameColumn('change_log', 'changes');
        });
    }
}
