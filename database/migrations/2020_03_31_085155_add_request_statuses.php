<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('metadata_request_status_types', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->string('label', 64);
        });

        DB::table('metadata_request_status_types')->insert([
            ['label' => 'New'],
            ['label' => 'Approved'],
            ['label' => 'Rejected'],
            ['label' => 'Complete']
        ]);

        Schema::table('help_requests', function (Blueprint $table) {
            $table->string('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metadata_request_status_types');
        Schema::table('help_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
