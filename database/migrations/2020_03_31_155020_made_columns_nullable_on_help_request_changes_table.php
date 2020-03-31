<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MadeColumnsNullableOnHelpRequestChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_request_changes', function (Blueprint $table) {
            $table->text('user_comment')->comment('Reason for change')->nullable()->change();
            $table->integer('user_id')->comment('User who made the change')->unsigned()->nullable()->change();
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
        });
    }
}
