<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedNewColumnsToHelpRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_requests', function (Blueprint $table) {
            //
            $table->text('other_needs')->nullable()->after("current_needs");
            $table->text('caller_observations')->nullable()->after('other_needs');
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
            //
            $table->dropColumn('other_needs');
            $table->dropColumn('caller_observations');
        });
    }
}
