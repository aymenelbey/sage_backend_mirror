<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class DataTechnTmbsToArray extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_tmbs', function (Blueprint $table) {
            DB::statement('ALTER TABLE data_techn_tmbs DROP constraint IF EXISTS data_techn_tmbs_technologie_foreign');
            DB::statement('ALTER TABLE data_techn_tmbs DROP constraint IF EXISTS "data_techn_tmbs_valorisationenergitique_foreign"');
            DB::statement('ALTER TABLE data_techn_tmbs DROP constraint IF EXISTS "data_techn_tmbs_autreactivite_foreign"');
            DB::statement('ALTER TABLE data_techn_tmbs DROP constraint IF EXISTS "data_techn_tmbs_typedechetaccepter_foreign"');
            DB::statement('ALTER TABLE data_techn_tmbs ALTER COLUMN technologie TYPE varchar');
            DB::statement('ALTER TABLE data_techn_tmbs ALTER COLUMN "valorisationEnergitique" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_tmbs ALTER COLUMN "autreActivite" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_tmbs ALTER COLUMN "typeDechetAccepter" TYPE varchar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_tmbs', function (Blueprint $table) {
            //
        });
    }
}
