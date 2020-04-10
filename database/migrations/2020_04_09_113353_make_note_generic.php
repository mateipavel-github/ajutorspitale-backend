<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNoteGeneric extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::rename('help_request_notes', 'notes');
        Schema::table('notes', function (Blueprint $table) {
            $table->integer('help_request_id')
                        ->unsigned()
                        ->comment('Deprecated. Used to prevent data loss when rolling back migration')
                        ->change();
            $table->integer('item_id')->unsigned();
            $table->string('item_type');
        });

        DB::table('notes')->update(
            ['item_type' => 'App\HelpRequest'],
            ['item_id' => DB::raw('help_request_id')]
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('notes')->where('item_type', 'App\HelpRequest')->update(
            ['help_request_id' => DB::raw('item_id')]
        );

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('item_id');
            $table->dropColumn('item_type');
        });

        Schema::rename('notes', 'help_request_notes');

    }
}
