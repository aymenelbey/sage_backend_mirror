<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AtacheEpicToSyndicat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('epics', function (Blueprint $table) {
            DB::statement('ALTER TABLE epics ADD COLUMN IF NOT EXISTS id_syndicat INTEGER;');
            DB::statement('ALTER TABLE epics ADD CONSTRAINT syndicat_fk FOREIGN KEY (id_syndicat) REFERENCES syndicats (id_syndicat) MATCH FULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syndicat', function (Blueprint $table) {
            DB::statement('ALTER TABLE epics DROP COLUMN IF EXISTS id_syndicat;');
            DB::statement('ALTER TABLE epics DROP CONSTRAINT IF EXISTS syndicat_fk;');
        });
    }
}
