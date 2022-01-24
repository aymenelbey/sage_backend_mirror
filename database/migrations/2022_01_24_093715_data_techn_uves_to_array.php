<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



class DataTechnUvesToArray extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_uves', function (Blueprint $table) {
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS data_techn_uves_typedechetrecus_foreign');
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS "data_techn_uves_reactif_foreign"');
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS "data_techn_uves_traitementfumee_foreign"');
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS "data_techn_uves_traitementnox_foreign"');
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS "data_techn_uves_equipeprocesstf_foreign"');
            DB::statement('ALTER TABLE data_techn_uves DROP constraint IF EXISTS "data_techn_uves_installationcomplementair_foreign"');

            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "typeDechetRecus" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "typeFoursChaudiere" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "traitementFumee" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "equipeProcessTF" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "reactif" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "traitementNOX" TYPE varchar');
            DB::statement('ALTER TABLE data_techn_uves ALTER COLUMN "installationComplementair" TYPE varchar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('array', function (Blueprint $table) {
            //
        });
    }
}
