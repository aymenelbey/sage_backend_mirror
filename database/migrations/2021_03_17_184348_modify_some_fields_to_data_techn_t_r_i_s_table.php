<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySomeFieldsToDataTechnTRISTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_tris', function (Blueprint $table) {
            $table->string("capaciteHoraire")->nullable()->change();
            $table->string("capaciteNominale")->nullable()->change();
            $table->string("capaciteReglementaire")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_t_r_i_s', function (Blueprint $table) {
            //
        });
    }
}