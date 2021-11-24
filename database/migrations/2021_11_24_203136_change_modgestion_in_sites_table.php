<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeModgestionInSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            DB::transaction(function () {
                DB::statement('ALTER TABLE sites DROP CONSTRAINT sites_modeGestion_check;');
                DB::statement('ALTER TABLE sites ADD CONSTRAINT sites_modeGestion_check CHECK (modeGestion::TEXT = ANY (ARRAY[\'Gestion privée\'::CHARACTER VARYING, \'Prestation de service\'::CHARACTER VARYING, \'Regie\'::CHARACTER VARYING,\'DSP\'::CHARACTER VARYING, \'MPS\'::CHARACTER VARYING,\'MGP\'::CHARACTER VARYING]::TEXT[]))');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            //
        });
    }
}
