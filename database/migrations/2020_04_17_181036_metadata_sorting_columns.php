<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MetadataSortingColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /* find metadata tables */
        $tables = DB::select('SHOW TABLES LIKE "metadata_%"');
        $tables = array_map('current', $tables);
        foreach($tables as $table) {
            Schema::table($table, function(Blueprint $table) {
                $table->integer('sort_order')->unsigned()->default(0);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
