<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AtacheEpicToSyndicatData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rows = explode("\n", file_get_contents(__DIR__.'/020921_composition_syndicats.csv'));
        $sql = "";
        $rows = array_slice($rows, 1, -1);
        foreach($rows as $row){
            $row = array_map(function($col){
                return trim(rtrim($col));
            }, explode(",", $row));
            $sql .= 'UPDATE epics SET id_syndicat = (SELECT id_syndicat from syndicats where sinoe = \''.$row[2].'\') where sinoe = \''.$row[0].'\';';
        }
        DB::raw($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('epics', function (Blueprint $table) {
            DB::statement("UPDATE epics SET id_syndicat = null");
        });
    }
}
