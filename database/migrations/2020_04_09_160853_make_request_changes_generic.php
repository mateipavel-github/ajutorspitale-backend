<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRequestChangesGeneric extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::dropIfExists('posting_changes');
        Schema::dropIfExists('posting_change_needs');

        Schema::create('posting_changes', function (Blueprint $table) {

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->integer('change_type_id');
            $table->integer('item_id')->unsigned();
            $table->string('item_type');
            $table->longText('change_log')->comment('JSON with this change\'s new values');
            $table->integer('user_id')->comment('User who made the change')->nullable();
            $table->text('user_comment')->comment('Reason for change')->nullable();
            $table->set('status', ['new','approved','rejected','in_progress','final'])->default('new');

        });

        Schema::create('posting_change_needs', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('posting_change_id');
            $table->integer('need_type_id');
            $table->integer('quantity');
        });

        $help_request_changes = DB::table('help_request_changes')->get()->toArray();
        $help_request_changes = array_map(function ($hrc) {
            $hrc->item_id = $hrc->help_request_id;
            $hrc->item_type = 'App\HelpRequest';
            unset($hrc->help_request_id);
            return (array)$hrc;
        }, $help_request_changes);

        $help_request_change_needs = DB::table('help_request_change_needs')->get()->toArray();
        $help_request_change_needs = array_map(function ($hrcn) {
            $hrcn->posting_change_id = $hrcn->help_request_change_id;
            unset($hrcn->help_request_change_id);
            return (array)$hrcn;
        }, $help_request_change_needs);

        DB::table('posting_changes')->insert($help_request_changes);
        DB::table('posting_change_needs')->insert($help_request_change_needs);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('posting_changes');
        Schema::dropIfExists('posting_change_needs');
    }
}
